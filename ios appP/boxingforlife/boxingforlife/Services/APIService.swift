import Foundation  // Para URLSession, JSONEncoder/Decoder, etc.
import Network     // Para NWPathMonitor (monitorização de rede)
import UIKit       // Para UIImage e operações de imagem
enum APIError: Error {
    
    case invalidURL
    case noData
    case decodingError
    case invalidResponse
    case serverError(String)
    case networkError(String)
    case connectionError(String)
    case imageLoadError(String)
}

private class ImageLoadingTask {
    let task: Task<UIImage, Error>
    var isCompleted = false
    init(_ task: Task<UIImage, Error>) {
        self.task = task
        // isCompleted inicia como false (task ainda em execução)
    }
}


class APIService: ObservableObject {
   
    static let shared = APIService()
    
    
    static let baseURL = "http://localhost:8080/api"
    
    
    static let imageBaseURL = "http://localhost/PAP"
    
   
    private let monitor = NWPathMonitor()
    
    
    private let queue = DispatchQueue(label: "NetworkMonitor")
    
   
    @Published private(set) var isConnected = false
    
    
    private let imageCache = NSCache<NSString, UIImage>()
    
    
    private var imageLoadingTasks: [String: ImageLoadingTask] = [:]
    
    
    private let taskLock = NSLock()
    
   
    private init() {
        setupNetworkMonitoring()
        setupImageCache()
    }
   
    private func setupImageCache() {
        imageCache.countLimit = 100 // Máximo de 100 imagens em cache
        imageCache.totalCostLimit = 50 * 1024 * 1024 // Limite de 50MB de memória
    }
    
    private func setupNetworkMonitoring() {
        // Define o callback que é executado sempre que o estado da rede muda
        monitor.pathUpdateHandler = { [weak self] path in
            let newStatus = path.status == .satisfied
            
            // Otimização: só atualiza se o estado realmente mudou
            // Evita notificações desnecessárias às Views observadoras
            if self?.isConnected != newStatus {
                self?.isConnected = newStatus
                print("🌐 Status da conexão alterado: \(newStatus ? "✅ conectado" : "❌ desconectado")")
            }
        }
        
        // Inicia o monitoramento numa fila separada (não bloqueia a main thread)
        monitor.start(queue: queue)
        
        // Verifica e define o estado inicial da conexão imediatamente
        let path = monitor.currentPath
        isConnected = path.status == .satisfied
        print("🚀 Estado inicial da conexão: \(isConnected ? "✅ conectado" : "❌ desconectado")")
    }
    
    func processImageURL(_ urlString: String) -> String {
        print("🔄 Processando URL original: \(urlString)")
        
        // Se a URL já está completa (começa com http), retorna como está
        // Evita processamento desnecessário de URLs já válidas
        if urlString.hasPrefix("http") {
            print("✅ URL já é completa, retornando: \(urlString)")
            return urlString
        }
        
        // Remove barra inicial se existir para normalizar o formato
        // Exemplo: "/imagem.jpg" → "imagem.jpg"
        let cleanPath = urlString.hasPrefix("/") ? String(urlString.dropFirst()) : urlString
        print("🧹 Caminho limpo: \(cleanPath)")
        
        // Determina o tipo de imagem baseado no caminho e aplica lógica específica
        let finalPath: String
        if cleanPath.contains("blog") || cleanPath.contains("posts") {
            // Imagens de blog - mantém o caminho original pois têm estrutura própria
            finalPath = cleanPath
            print("📝 Detectada imagem de blog: \(cleanPath)")
        } else {
            // Imagens de produtos - adiciona prefixo se necessário
            // Garante que todas as imagens de produtos estão na pasta correta
            finalPath = cleanPath.hasPrefix("imagens/produtos/") ? cleanPath : "imagens/produtos/\(cleanPath)"
            print("🛍️ Detectada imagem de produto: \(finalPath)")
        }
        
        // Constrói a URL completa combinando servidor base + caminho processado
        let processedUrl = "\(Self.imageBaseURL)/\(finalPath)"
        print("🎯 URL processada final: \(processedUrl)")
        
        // Valida se a URL construída é válida antes de retornar
        // Se inválida, retorna a original como fallback
        guard URL(string: processedUrl) != nil else {
            print("⚠️ AVISO: URL processada não é válida: \(processedUrl)")
            return urlString // Retorna original se a processada for inválida
        }
        
        return processedUrl
    }
    
    
    private func handleResponse(_ response: HTTPURLResponse, data: Data) throws {
        // Verifica se o código de status indica sucesso
        // 200 = OK (operação realizada), 201 = Created (recurso criado)
        if response.statusCode != 200 && response.statusCode != 201 {
            // Tenta extrair mensagem de erro estruturada do JSON
            if let errorJson = try? JSONSerialization.jsonObject(with: data) as? [String: Any],
               let errorMessage = errorJson["error"] as? String {
                // Usa mensagem específica do servidor quando disponível
                throw APIError.serverError(errorMessage)
            } else {
                // Fallback: usa código de status quando não há mensagem específica
                throw APIError.serverError("Erro do servidor: \(response.statusCode)")
            }
        }
    }
    
    private func checkConnection() throws {
        // Otimização: se já está conectado, não precisa verificar novamente
        // Evita verificações desnecessárias quando a conexão está estável
        if isConnected {
            return
        }
        
        // Verifica o estado atual da conexão em tempo real
        // Importante porque o estado pode ter mudado desde a última verificação
        let path = monitor.currentPath
        isConnected = path.status == .satisfied
        
        // Se ainda não há conexão, impede operação e informa o utilizador
        if !isConnected {
            print("🚫 Sem conexão com a internet - operação bloqueada")
            throw APIError.connectionError("Sem conexão com a internet")
        }
    }
    
    func loadImage(from urlString: String) async throws -> UIImage {
        print("🖼️ Loading image from: \(urlString)")
        
        // OTIMIZAÇÃO 1: Verifica se a imagem já está no cache
        // Retorno instantâneo para melhor UX e economia de recursos
        if let cachedImage = imageCache.object(forKey: urlString as NSString) {
            print("⚡ Image found in cache")
            return cachedImage
        }
        
        // OTIMIZAÇÃO 2: Evita downloads duplicados da mesma imagem
        // Thread-safe access ao dicionário de tasks
        taskLock.lock()
        defer { taskLock.unlock() }
        
        if let existingTask = imageLoadingTasks[urlString] {
            if existingTask.isCompleted {
                // Remove task antiga que já terminou
                print("🧹 Removing completed task")
                imageLoadingTasks.removeValue(forKey: urlString)
            } else {
                // Reutiliza task existente em progresso
                print("🔄 Using existing task")
                return try await existingTask.task.value
            }
        }
        
        // Cria nova task de carregamento
        let task = Task<UIImage, Error> {
            // Processa a URL para garantir formato correto
            let processedURL = processImageURL(urlString)
            print("🔧 Processed URL: \(processedURL)")
            
            guard let url = URL(string: processedURL) else {
                print("❌ Invalid URL: \(urlString)")
                throw APIError.imageLoadError("URL inválida: \(urlString)")
            }
            
            do {
                // Executa o download da imagem
                let (data, response) = try await URLSession.shared.data(from: url)
                
                guard let httpResponse = response as? HTTPURLResponse else {
                    print("❌ Invalid response type")
                    throw APIError.imageLoadError("Resposta inválida do servidor")
                }
                
                print("📊 Image response status code: \(httpResponse.statusCode)")
                
                // Tratamento específico para imagem não encontrada
                if httpResponse.statusCode == 404 {
                    print("🔍 Imagem não encontrada: \(processedURL)")
                    throw APIError.imageLoadError("Imagem não encontrada")
                }
                
                // Verifica se o status code indica sucesso
                guard (200...299).contains(httpResponse.statusCode) else {
                    print("❌ Invalid status code: \(httpResponse.statusCode)")
                    throw APIError.imageLoadError("Erro ao carregar imagem: Status code inválido")
                }
                
                // Tenta criar UIImage a partir dos dados recebidos
                guard let image = UIImage(data: data) else {
                    print("❌ Failed to create image from data")
                    throw APIError.imageLoadError("Não foi possível criar imagem a partir dos dados")
                }
                
                print("✅ Successfully loaded image")
                
                // Adiciona a imagem ao cache para uso futuro
                imageCache.setObject(image, forKey: urlString as NSString)
                
                return image
            } catch let error as APIError {
                print("🚨 API Error during image loading: \(error)")
                throw error
            } catch {
                print("🌐 Network error during image loading: \(error)")
                throw APIError.imageLoadError("Erro de rede ao carregar imagem: \(error.localizedDescription)")
            }
        }
        
        // Armazena a task para possível reutilização
        let loadingTask = ImageLoadingTask(task)
        imageLoadingTasks[urlString] = loadingTask
        print("📝 Created new loading task")
        
        do {
            // Aguarda conclusão da task
            let image = try await task.value
            taskLock.lock()
            defer { taskLock.unlock() }
            
            // Marca task como completada e remove do dicionário
            loadingTask.isCompleted = true
            imageLoadingTasks.removeValue(forKey: urlString)
            print("🎉 Task completed successfully")
            return image
        } catch {
            taskLock.lock()
            defer { taskLock.unlock() }
            
            // Remove task falhada
            imageLoadingTasks.removeValue(forKey: urlString)
            print("💥 Task failed with error: \(error)")
            throw error
        }
    }
    
    func register(nome: String, email: String, palavra_passe: String, local_envio: String? = nil) async throws -> [String: Any] {
        try checkConnection()
        
        let url = URL(string: "\(APIService.baseURL)/auth/register")!
        var request = URLRequest(url: url)
        request.httpMethod = "POST"
        request.setValue("application/json", forHTTPHeaderField: "Content-Type")
        
        // Constrói o corpo da requisição com dados obrigatórios
        var body: [String: Any] = [
            "nome": nome,
            "email": email,
            "palavra_passe": palavra_passe
        ]
        
        // Adiciona local de envio apenas se fornecido
        if let local_envio = local_envio {
            body["local_envio"] = local_envio
        }
        
        request.httpBody = try JSONSerialization.data(withJSONObject: body)
        
        let (data, response) = try await URLSession.shared.data(for: request)
        
        guard let httpResponse = response as? HTTPURLResponse else {
            throw APIError.networkError("Resposta inválida do servidor")
        }
        
        try handleResponse(httpResponse, data: data)
        return try JSONSerialization.jsonObject(with: data) as! [String: Any]
    }
    
    func login(email: String, palavra_passe: String) async throws -> [String: Any] {
        let url = URL(string: "\(APIService.baseURL)/auth/login")!
        var request = URLRequest(url: url)
        request.httpMethod = "POST"
        request.setValue("application/json", forHTTPHeaderField: "Content-Type")
        
        let body: [String: Any] = [
            "email": email,
            "palavra_passe": palavra_passe
        ]
        
        request.httpBody = try JSONSerialization.data(withJSONObject: body)
        
        let (data, response) = try await URLSession.shared.data(for: request)
        
        guard let httpResponse = response as? HTTPURLResponse else {
            throw APIError.networkError("Resposta inválida do servidor")
        }
        
        print("🔐 Resposta do servidor - Status: \(httpResponse.statusCode)")
        
        if httpResponse.statusCode == 200 {
            if let json = try JSONSerialization.jsonObject(with: data) as? [String: Any] {
                print("✅ Login bem-sucedido: \(json)")
                // Combina os dados retornados com flag de sucesso
                var result = json
                result["success"] = true
                return result
            } else {
                throw APIError.serverError("Resposta inválida do servidor")
            }
        } else {
            throw APIError.serverError("Erro do servidor: \(httpResponse.statusCode)")
        }
    }
    
    
    func getProducts() async throws -> [[String: Any]] {
        print("🛍️ Iniciando carregamento de produtos...")
        try checkConnection()
        
        let url = URL(string: "\(APIService.baseURL)/products")!
        print("📡 Fetching products from: \(url.absoluteString)")
        
        var request = URLRequest(url: url)
        request.timeoutInterval = 30 // Timeout estendido para operações pesadas
        
        var retryCount = 0
        let maxRetries = 3
        
        // Sistema de retry para maior robustez
        while retryCount < maxRetries {
            do {
                print("🔄 Tentativa \(retryCount + 1) de \(maxRetries)")
                let (data, response) = try await URLSession.shared.data(for: request)
                
                guard let httpResponse = response as? HTTPURLResponse else {
                    print("❌ Invalid response type")
                    throw APIError.networkError("Resposta inválida do servidor")
                }
                
                print("📊 Response status code: \(httpResponse.statusCode)")
                
                if httpResponse.statusCode == 200 {
                    if var products = try? JSONSerialization.jsonObject(with: data) as? [[String: Any]] {
                        print("✅ Successfully parsed \(products.count) products")
                        
                        // Processa URLs das imagens para todas os produtos
                        for i in 0..<products.count {
                            if let imageUrl = products[i]["imagem"] as? String {
                                let processedUrl = processImageURL(imageUrl)
                                products[i]["imagem"] = processedUrl
                                print("🖼️ Processed image URL: \(processedUrl)")
                            }
                        }
                        
                        return products
                    } else {
                        print("❌ Failed to parse products JSON")
                        throw APIError.decodingError
                    }
                } else {
                    print("🚨 Server error: \(httpResponse.statusCode)")
                    if let errorJson = try? JSONSerialization.jsonObject(with: data) as? [String: Any],
                       let errorMessage = errorJson["error"] as? String {
                        throw APIError.serverError(errorMessage)
                    } else {
                        throw APIError.serverError("Erro do servidor: \(httpResponse.statusCode)")
                    }
                }
            } catch {
                print("💥 Erro ao buscar produtos: \(error)")
                retryCount += 1
                if retryCount < maxRetries {
                    print("⏳ Tentando novamente em 1 segundo...")
                    try await Task.sleep(nanoseconds: 1_000_000_000) // 1 segundo
                    continue
                } else {
                    print("🔴 Número máximo de tentativas atingido")
                    throw error
                }
            }
        }
        
        throw APIError.networkError("Número máximo de tentativas atingido")
    }
    
    func getProductDetails(codigo_base: String) async throws -> [[String: Any]] {
        try checkConnection()
        
        let url = URL(string: "\(APIService.baseURL)/products/\(codigo_base)")!
        
        let (data, response) = try await URLSession.shared.data(from: url)
        
        guard let httpResponse = response as? HTTPURLResponse else {
            throw APIError.networkError("Resposta inválida do servidor")
        }
        
        try handleResponse(httpResponse, data: data)
        var details = try JSONSerialization.jsonObject(with: data) as! [[String: Any]]
        
        // Processar URLs das imagens
        for i in 0..<details.count {
            if let imageUrl = details[i]["imagem"] as? String {
                details[i]["imagem"] = processImageURL(imageUrl)
            }
        }
        
        return details
    }
    
    // MARK: - Cart
    func addToCart(id_utilizador: Int?, session_id: String?, sku: String, quantidade: Int) async throws -> [String: Any] {
        let url = URL(string: "\(APIService.baseURL)/cart")!
        var request = URLRequest(url: url)
        request.httpMethod = "POST"
        request.setValue("application/json", forHTTPHeaderField: "Content-Type")
        
        // Criar o corpo da requisição
        var body: [String: Any] = [
            "sku": sku,
            "quantidade": quantidade
        ]
        
        // Adicionar apenas id_utilizador ou session_id, nunca ambos
        if let userId = id_utilizador, userId > 0 {
            body["id_utilizador"] = userId
        } else if let sessionId = session_id {
            body["session_id"] = sessionId
        }
        
        print("Enviando requisição para adicionar ao carrinho:")
        print("URL: \(url)")
        print("Body: \(body)")
        
        request.httpBody = try JSONSerialization.data(withJSONObject: body)
        
        let (data, response) = try await URLSession.shared.data(for: request)
        
        guard let httpResponse = response as? HTTPURLResponse else {
            throw APIError.networkError("Resposta inválida do servidor")
        }
        
        print("Resposta do servidor - Status: \(httpResponse.statusCode)")
        print("Resposta do servidor - Data: \(String(data: data, encoding: .utf8) ?? "")")
        
        // Tratar tanto 200 quanto 201 como sucesso
        if httpResponse.statusCode == 200 || httpResponse.statusCode == 201 {
            if let json = try JSONSerialization.jsonObject(with: data) as? [String: Any] {
                print("Resposta JSON: \(json)")
                return json
            } else {
                throw APIError.serverError("Resposta inválida do servidor")
            }
        } else {
            throw APIError.serverError("Erro do servidor: \(httpResponse.statusCode)")
        }
    }
    
    func getCart(id_utilizador: Int) async throws -> [[String: Any]] {
        try checkConnection()
        
        let url = URL(string: "\(APIService.baseURL)/cart/\(id_utilizador)")!
        print("Fetching cart for user: \(id_utilizador)")
        
        let (data, response) = try await URLSession.shared.data(from: url)
        
        guard let httpResponse = response as? HTTPURLResponse else {
            throw APIError.networkError("Resposta inválida do servidor")
        }
        
        print("Cart response status code: \(httpResponse.statusCode)")
        print("Cart response data: \(String(data: data, encoding: .utf8) ?? "")")
        
        if httpResponse.statusCode == 200 {
            if let items = try? JSONSerialization.jsonObject(with: data) as? [[String: Any]] {
                print("Successfully parsed \(items.count) cart items")
                print("Cart items: \(items)")
                return items
            } else if let json = try? JSONSerialization.jsonObject(with: data) as? [String: Any],
                      let items = json["items"] as? [[String: Any]] {
                print("Successfully parsed \(items.count) cart items from items key")
                print("Cart items: \(items)")
                return items
            } else {
                print("Failed to parse cart items")
                throw APIError.serverError("Resposta inválida do servidor")
            }
        } else if httpResponse.statusCode == 404 {
            print("Carrinho vazio para o usuário")
            return []
        } else {
            print("Server error: \(httpResponse.statusCode)")
            throw APIError.serverError("Erro do servidor: \(httpResponse.statusCode)")
        }
    }
    
    func transferCart(fromSessionId: String, toUserId: Int) async throws {
        let url = URL(string: "\(APIService.baseURL)/cart/transfer")!
        var request = URLRequest(url: url)
        request.httpMethod = "POST"
        request.setValue("application/json", forHTTPHeaderField: "Content-Type")
        
        // Apenas os dados necessários para a atualização do carrinho
        let body: [String: Any] = [
            "session_id": fromSessionId,
            "id_utilizador": toUserId
        ]
        
        request.httpBody = try JSONSerialization.data(withJSONObject: body)
        
        let (data, response) = try await URLSession.shared.data(for: request)
        
        guard let httpResponse = response as? HTTPURLResponse else {
            throw APIError.networkError("Resposta inválida do servidor")
        }
        
        if httpResponse.statusCode != 200 {
            throw APIError.serverError("Erro ao transferir carrinho")
        }
    }

    func getCartBySession(sessionId: String) async throws -> [[String: Any]] {
        try checkConnection()
        
        let url = URL(string: "\(APIService.baseURL)/cart/session/\(sessionId)")!
        print("Fetching cart for session: \(sessionId)")
        
        let (data, response) = try await URLSession.shared.data(from: url)
        
        guard let httpResponse = response as? HTTPURLResponse else {
            throw APIError.networkError("Resposta inválida do servidor")
        }
        
        print("Cart response status code: \(httpResponse.statusCode)")
        print("Cart response data: \(String(data: data, encoding: .utf8) ?? "")")
        
        if httpResponse.statusCode == 200 {
            if let items = try? JSONSerialization.jsonObject(with: data) as? [[String: Any]] {
                print("Successfully parsed \(items.count) cart items")
                print("Cart items: \(items)")
                return items
            } else {
                print("Failed to parse cart items")
                throw APIError.serverError("Resposta inválida do servidor")
            }
        } else if httpResponse.statusCode == 404 {
            print("Carrinho vazio para a sessão")
            return []
        } else {
            print("Server error: \(httpResponse.statusCode)")
            throw APIError.serverError("Erro do servidor: \(httpResponse.statusCode)")
        }
    }
    
    // MARK: - Blog
    func getBlogPosts() async throws -> [[String: Any]] {
        try checkConnection()
        
        let url = URL(string: "\(APIService.baseURL)/blog")!
        
        let (data, response) = try await URLSession.shared.data(from: url)
        
        guard let httpResponse = response as? HTTPURLResponse else {
            throw APIError.networkError("Resposta inválida do servidor")
        }
        
        try handleResponse(httpResponse, data: data)
        var posts = try JSONSerialization.jsonObject(with: data) as! [[String: Any]]
        
        // Processar URLs das imagens
        for i in 0..<posts.count {
            if let imageUrl = posts[i]["imagem"] as? String {
                posts[i]["imagem"] = processImageURL(imageUrl)
            }
        }
        
        return posts
    }
    
    func getBlogPost(id: Int) async throws -> [String: Any] {
        try checkConnection()
        
        let url = URL(string: "\(APIService.baseURL)/blog/\(id)")!
        
        let (data, response) = try await URLSession.shared.data(from: url)
        
        guard let httpResponse = response as? HTTPURLResponse else {
            throw APIError.networkError("Resposta inválida do servidor")
        }
        
        try handleResponse(httpResponse, data: data)
        var post = try JSONSerialization.jsonObject(with: data) as! [String: Any]
        
        // Processar URL da imagem
        if let imageUrl = post["imagem"] as? String {
            post["imagem"] = processImageURL(imageUrl)
        }
        
        return post
    }

    func createBlogPost(title: String, content: String, image: Data?, userId: Int) async throws -> [String: Any] {
        try checkConnection()
        
        let url = URL(string: "\(APIService.baseURL)/blog")!
        
        // Preparar o multipart form data para upload da imagem
        let boundary = UUID().uuidString
        
        var request = URLRequest(url: url)
        request.httpMethod = "POST"
        request.setValue("multipart/form-data; boundary=\(boundary)", forHTTPHeaderField: "Content-Type")
        
        var body = Data()
        
        // Adicionar campos de texto
        body.append("--\(boundary)\r\n".data(using: .utf8)!)
        body.append("Content-Disposition: form-data; name=\"titulo\"\r\n\r\n".data(using: .utf8)!)
        body.append("\(title)\r\n".data(using: .utf8)!)
        
        body.append("--\(boundary)\r\n".data(using: .utf8)!)
        body.append("Content-Disposition: form-data; name=\"resumo\"\r\n\r\n".data(using: .utf8)!)
        body.append("\(String(content.prefix(200)))\r\n".data(using: .utf8)!) // Usar os primeiros 200 caracteres como resumo
        
        body.append("--\(boundary)\r\n".data(using: .utf8)!)
        body.append("Content-Disposition: form-data; name=\"conteudo\"\r\n\r\n".data(using: .utf8)!)
        body.append("\(content)\r\n".data(using: .utf8)!)
        
        body.append("--\(boundary)\r\n".data(using: .utf8)!)
        body.append("Content-Disposition: form-data; name=\"id_utilizador\"\r\n\r\n".data(using: .utf8)!)
        body.append("\(userId)\r\n".data(using: .utf8)!)
        
        // Adicionar imagem se disponível
        if let imageData = image {
            body.append("--\(boundary)\r\n".data(using: .utf8)!)
            body.append("Content-Disposition: form-data; name=\"imagem\"; filename=\"blog_image.jpg\"\r\n".data(using: .utf8)!)
            body.append("Content-Type: image/jpeg\r\n\r\n".data(using: .utf8)!)
            body.append(imageData)
            body.append("\r\n".data(using: .utf8)!)
        }
        
        // Finalizar o body
        body.append("--\(boundary)--\r\n".data(using: .utf8)!)
        
        request.httpBody = body
        
        let (data, response) = try await URLSession.shared.data(for: request)
        
        guard let httpResponse = response as? HTTPURLResponse else {
            throw APIError.networkError("Resposta inválida do servidor")
        }
        
        print("Resposta de criação de blog - Status: \(httpResponse.statusCode)")
        
        if httpResponse.statusCode == 201 {
            if let json = try? JSONSerialization.jsonObject(with: data) as? [String: Any] {
                print("Blog criado com sucesso: \(json)")
                return json
            } else {
                throw APIError.serverError("Resposta inválida do servidor")
            }
        } else {
            // Tentar ler a mensagem de erro do servidor
            if let errorData = try? JSONSerialization.jsonObject(with: data) as? [String: Any],
               let errorMessage = errorData["error"] as? String {
                throw APIError.serverError(errorMessage)
            } else {
                throw APIError.serverError("Erro do servidor: \(httpResponse.statusCode)")
            }
        }
    }

    func getBlogComments(postId: Int) async throws -> [[String: Any]] {
        try checkConnection()
        
        // Tenta diferentes formatos de URL para garantir compatibilidade com o servidor
        let endpoints = [
            "\(APIService.baseURL)/blog/\(postId)/comments",
            "\(APIService.baseURL)/blog/comments/\(postId)"
        ]
        
        var lastError: Error? = nil
        
        // Tenta cada URL possível
        for endpoint in endpoints {
            do {
                let url = URL(string: endpoint)!
                print("Tentando buscar comentários do post ID: \(postId) em: \(endpoint)")
                
                let (data, response) = try await URLSession.shared.data(from: url)
                
                guard let httpResponse = response as? HTTPURLResponse else {
                    throw APIError.networkError("Resposta inválida do servidor")
                }
                
                print("Resposta de comentários do blog - Status: \(httpResponse.statusCode)")
                print("Dados recebidos: \(String(data: data, encoding: .utf8) ?? "")")
                
                // Caso de sucesso - dados encontrados
                if httpResponse.statusCode == 200 {
                    if let comments = try? JSONSerialization.jsonObject(with: data) as? [[String: Any]] {
                        print("Encontrados \(comments.count) comentários para o post \(postId)")
                        return comments
                    } else if let comment = try? JSONSerialization.jsonObject(with: data) as? [String: Any] {
                        print("Encontrado 1 comentário retornado como objeto")
                        // Se o servidor retornar um único objeto em vez de um array
                        return [comment]
                    }
                }
                
                // Caso em que o servidor retorna 404 ou outra mensagem
                if httpResponse.statusCode == 404 || httpResponse.statusCode == 204 {
                    print("Nenhum comentário encontrado para o post \(postId)")
                    return []
                }
                
            } catch {
                lastError = error
                print("Erro ao buscar comentários usando \(endpoint): \(error.localizedDescription)")
                // Continua para tentar a próxima URL
            }
        }
        
        // Se chegamos aqui, nenhuma URL funcionou
        print("Não foi possível buscar comentários. Retornando lista vazia.")
        return []
    }

   
    func addBlogComment(postId: Int, userId: Int, comment: String) async throws -> [String: Any] {
        // Valida conectividade antes de prosseguir
        try checkConnection()
        
        // Usa o endpoint correto baseado na estrutura do servidor
        // A rota correta é /blog/:id/comments onde :id é o ID do post
        let endpoint = "\(APIService.baseURL)/blog/\(postId)/comments"
        
        var lastError: Error? = nil
        
        // Cria o corpo da requisição com todos os formatos possíveis de campo para garantir compatibilidade
        // Duplicação deliberada de campos para suportar diferentes convenções de nomenclatura
        let body: [String: Any] = [
            "id_post": postId,        // Formato snake_case (padrão PHP/MySQL)
            "postId": postId,         // Formato camelCase (padrão JavaScript)
            "id_utilizador": userId,  // Formato snake_case
            "userId": userId,         // Formato camelCase
            "conteudo": comment,      // Campo em português
            "content": comment        // Campo em inglês
        ]
        
        print("Enviando comentário para o blog \(postId): \(body)")
        
        do {
            // Configuração da requisição HTTP POST
            let url = URL(string: endpoint)!
            var request = URLRequest(url: url)
            request.httpMethod = "POST"
            request.setValue("application/json", forHTTPHeaderField: "Content-Type")
            request.httpBody = try JSONSerialization.data(withJSONObject: body)
            
            print("Enviando comentário para: \(endpoint)")
            
            // Execução da requisição
            let (data, response) = try await URLSession.shared.data(for: request)
            
            guard let httpResponse = response as? HTTPURLResponse else {
                throw APIError.networkError("Resposta inválida do servidor")
            }
            
            print("Resposta do servidor ao adicionar comentário - Status: \(httpResponse.statusCode)")
            print("Resposta: \(String(data: data, encoding: .utf8) ?? "")")
            
            // Aceita status codes 200 ou 201 como sucesso
            // 200 = OK (atualização), 201 = Created (criação)
            if httpResponse.statusCode == 200 || httpResponse.statusCode == 201 {
                if let json = try? JSONSerialization.jsonObject(with: data) as? [String: Any] {
                    print("Comentário adicionado com sucesso: \(json)")
                    return json
                } else {
                    // Retorna uma resposta padrão se o servidor não retornar um JSON válido
                    // Alguns servidores podem retornar apenas status code sem corpo
                    print("Comentário adicionado com sucesso (sem JSON retornado)")
                    return ["success": true, "message": "Comentário adicionado com sucesso"]
                }
            } else {
                // Tenta extrair mensagem de erro do servidor
                if let errorData = try? JSONSerialization.jsonObject(with: data) as? [String: Any],
                   let errorMessage = errorData["error"] as? String {
                    throw APIError.serverError(errorMessage)
                } else {
                    throw APIError.serverError("Erro do servidor: \(httpResponse.statusCode)")
                }
            }
        } catch {
            print("Erro ao adicionar comentário: \(error.localizedDescription)")
            throw error
        }
    }
    
    
    func deleteBlogPost(blogId: Int, userId: Int) async throws -> [String: Any] {
        // Valida conectividade antes da operação de eliminação
        try checkConnection()
        
        // Constrói URL específica para a publicação a eliminar
        let url = URL(string: "\(APIService.baseURL)/blog/\(blogId)")!
        
        // Configuração da requisição DELETE
        var request = URLRequest(url: url)
        request.httpMethod = "DELETE"  // Método HTTP para eliminação
        request.setValue("application/json", forHTTPHeaderField: "Content-Type")
        
        // Corpo da requisição com ID do utilizador para validação de autorização
        let body = ["id_utilizador": userId]
        request.httpBody = try JSONSerialization.data(withJSONObject: body)
        
        // Execução da requisição de eliminação
        let (data, response) = try await URLSession.shared.data(for: request)
        
        guard let httpResponse = response as? HTTPURLResponse else {
            throw APIError.networkError("Resposta inválida do servidor")
        }
        
        // Verifica se eliminação foi bem-sucedida (status 200)
        if httpResponse.statusCode == 200 {
            // Tenta extrair resposta JSON do servidor
            if let json = try? JSONSerialization.jsonObject(with: data) as? [String: Any] {
                return json
            } else {
                // Fallback se servidor não retornar JSON válido
                return ["success": true, "message": "Blog eliminado com sucesso"]
            }
        } else {
            // Tratamento de erros - tenta extrair mensagem específica do servidor
            if let errorData = try? JSONSerialization.jsonObject(with: data) as? [String: Any],
               let errorMessage = errorData["error"] as? String {
                throw APIError.serverError(errorMessage)
            } else {
                throw APIError.serverError("Erro ao eliminar blog: \(httpResponse.statusCode)")
            }
        }
    }
    
   
    func deleteBlogComment(commentId: Int, userId: Int) async throws -> [String: Any] {
        // Valida conectividade antes da operação de eliminação
        try checkConnection()
        
        // Constrói URL específica para o comentário a eliminar
        let url = URL(string: "\(APIService.baseURL)/blog/comments/\(commentId)")!
        
        // Configuração da requisição DELETE
        var request = URLRequest(url: url)
        request.httpMethod = "DELETE"  // Método HTTP para eliminação
        request.setValue("application/json", forHTTPHeaderField: "Content-Type")
        
        // Corpo da requisição com ID do utilizador para validação
        let body = ["id_utilizador": userId]
        request.httpBody = try JSONSerialization.data(withJSONObject: body)
        
        // Execução da requisição de eliminação
        let (data, response) = try await URLSession.shared.data(for: request)
        
        guard let httpResponse = response as? HTTPURLResponse else {
            throw APIError.networkError("Resposta inválida do servidor")
        }
        
        // Verifica se eliminação foi bem-sucedida (status 200)
        if httpResponse.statusCode == 200 {
            // Tenta extrair resposta JSON do servidor
            if let json = try? JSONSerialization.jsonObject(with: data) as? [String: Any] {
                return json
            } else {
                // Fallback se servidor não retornar JSON válido
                return ["success": true, "message": "Comentário eliminado com sucesso"]
            }
        } else {
            // Tratamento de erros - extrai mensagem específica do servidor se disponível
            if let errorData = try? JSONSerialization.jsonObject(with: data) as? [String: Any],
               let errorMessage = errorData["error"] as? String {
                throw APIError.serverError(errorMessage)
            } else {
                throw APIError.serverError("Erro ao eliminar comentário: \(httpResponse.statusCode)")
            }
        }
    }
    
    
    func addReview(id_utilizador: Int, mensagem: String, avaliacao: Int, id_encomenda: Int) async throws -> [String: Any] {
        // Valida conectividade antes de criar review
        try checkConnection()
        
        // Endpoint para criação de reviews
        let url = URL(string: "\(APIService.baseURL)/reviews")!
        var request = URLRequest(url: url)
        request.httpMethod = "POST"  // Criação de novo recurso
        request.setValue("application/json", forHTTPHeaderField: "Content-Type")
        
        // Estrutura dos dados da avaliação
        let body = [
            "id_utilizador": id_utilizador,    // Quem está a avaliar
            "mensagem": mensagem,              // Texto da avaliação
            "avaliacao": avaliacao,            // Pontuação (ex: 1-5 estrelas)
            "id_encomenda": id_encomenda       // Encomenda que justifica a review
        ] as [String: Any]
        
        // Serializa dados para JSON
        request.httpBody = try JSONSerialization.data(withJSONObject: body)
        
        // Executa requisição de criação
        let (data, response) = try await URLSession.shared.data(for: request)
        
        guard let httpResponse = response as? HTTPURLResponse else {
            throw APIError.networkError("Resposta inválida do servidor")
        }
        
        // Valida resposta usando função auxiliar
        try handleResponse(httpResponse, data: data)
        
        // Converte resposta para dicionário e retorna
        return try JSONSerialization.jsonObject(with: data) as! [String: Any]
    }
    
   
    func getReviews() async throws -> [[String: Any]] {
        // Valida conectividade antes de buscar reviews
        try checkConnection()
        
        // Endpoint para listagem de todas as reviews
        let url = URL(string: "\(APIService.baseURL)/reviews")!
        
        // Executa requisição GET simples
        let (data, response) = try await URLSession.shared.data(from: url)
        
        guard let httpResponse = response as? HTTPURLResponse else {
            throw APIError.networkError("Resposta inválida do servidor")
        }
        
        // Valida resposta usando função auxiliar
        try handleResponse(httpResponse, data: data)
        
        // Converte resposta para array de dicionários e retorna
        return try JSONSerialization.jsonObject(with: data) as! [[String: Any]]
    }
    
    
    func getOrders(id_utilizador: Int) async throws -> [Order] {
        // Constrói URL com parâmetro de consulta para filtrar por utilizador
        let url = URL(string: "\(APIService.baseURL)/orders?id_utilizador=\(id_utilizador)")!
        
        // Executa requisição GET
        let (data, response) = try await URLSession.shared.data(from: url)
        
        guard let httpResponse = response as? HTTPURLResponse else {
            throw APIError.networkError("Resposta inválida do servidor")
        }
        
        // Valida resposta HTTP
        try handleResponse(httpResponse, data: data)
        
        // Utiliza JSONDecoder para parsing automático para objetos Order
        let decoder = JSONDecoder()
        let orders = try decoder.decode([Order].self, from: data)
        return orders
    }
    
    
    func createOrder(orderData: [String: Any]) async throws -> Order {
        // Endpoint para criação de encomendas
        let url = URL(string: "\(APIService.baseURL)/orders")!
        var request = URLRequest(url: url)
        request.httpMethod = "POST"  // Criação de novo recurso
        request.setValue("application/json", forHTTPHeaderField: "Content-Type")
        
        // Serializa dados da encomenda para JSON
        request.httpBody = try JSONSerialization.data(withJSONObject: orderData)
        
        // Executa requisição de criação
        let (data, response) = try await URLSession.shared.data(for: request)
        
        guard let httpResponse = response as? HTTPURLResponse else {
            throw APIError.networkError("Resposta inválida do servidor")
        }
        
        print("Resposta do servidor - Status: \(httpResponse.statusCode)")
        print("Resposta do servidor - Dados: \(String(data: data, encoding: .utf8) ?? "")")
        
        // Valida que encomenda foi criada com sucesso (status 201 = Created)
        if httpResponse.statusCode == 201 {
            // O servidor retorna apenas o ID da encomenda criada
            if let json = try? JSONSerialization.jsonObject(with: data) as? [String: Any],
               let orderId = json["orderId"] as? Int {
                
                // Constrói um objeto Order básico com os dados que temos
                // Combina dados enviados (orderData) com ID retornado pelo servidor
                return Order(
                    id: orderId,                                          // ID atribuído pelo servidor
                    id_utilizador: orderData["id_utilizador"] as? Int ?? 0,  // ID do comprador
                    items: nil,                                           // Itens serão carregados posteriormente se necessário
                    total: orderData["total"] as? Double ?? 0,           // Valor total pago
                    local_envio: orderData["local_envio"] as? String,    // Endereço de entrega
                    data_encomenda: nil,                                 // Data será definida pelo servidor
                    status: "pendente"                                   // Status inicial padrão
                )
            } else {
                throw APIError.serverError("Resposta inválida do servidor: ID da encomenda não encontrado")
            }
        } else {
            throw APIError.serverError("Erro do servidor: \(httpResponse.statusCode)")
        }
    }
    
   
    func getUserProfile(id: Int) async throws -> [String: Any] {
        // Valida conectividade antes de buscar perfil
        try checkConnection()
        
        // Constrói URL específica para o utilizador
        let url = URL(string: "\(APIService.baseURL)/users/\(id)")!
        print("[API] Buscando perfil do usuário: \(id)")
        
        // Executa requisição GET simples
        let (data, response) = try await URLSession.shared.data(from: url)
        
        guard let httpResponse = response as? HTTPURLResponse else {
            throw APIError.networkError("Resposta inválida do servidor")
        }
        
        // Logging detalhado da resposta para debugging
        print("[API] User profile response status code: \(httpResponse.statusCode)")
        print("[API] User profile response data: \(String(data: data, encoding: .utf8) ?? "")")
        
        // Verifica se perfil foi encontrado com sucesso
        if httpResponse.statusCode == 200 {
            if let userData = try? JSONSerialization.jsonObject(with: data) as? [String: Any] {
                print("[API] Dados do usuário obtidos com sucesso: \(userData)")
                
                // Verificação específica do endereço de envio (campo crítico para checkout)
                if let localEnvio = userData["local_envio"] as? String {
                    print("[API] Endereço de envio encontrado na API: \(localEnvio)")
                } else {
                    print("[API] Endereço de envio NÃO encontrado na resposta da API")
                }
                
                return userData
            } else {
                print("[API] Falha ao analisar dados do usuário")
                throw APIError.serverError("Resposta inválida do servidor")
            }
        } else {
            print("[API] Erro do servidor: \(httpResponse.statusCode)")
            throw APIError.serverError("Erro do servidor: \(httpResponse.statusCode)")
        }
    }
    
    /**
     * Atualiza dados do perfil de um utilizador
     * 
     * Permite modificar informações do perfil como nome, email,
     * endereço de envio e outras preferências do utilizador.
     * 
     * FUNCIONALIDADES:
     * - Atualização parcial ou completa de dados
     * - Validação de dados no servidor
     * - Logging detalhado do processo
     * - Confirmação de atualização bem-sucedida
     * - Tratamento de erros específicos
     * 
     * CAMPOS TIPICAMENTE ATUALIZÁVEIS:
     * - nome: Nome completo
     * - email: Endereço de email
     * - local_envio: Endereço de entrega preferido
     * - palavra_passe: Nova password (se fornecida)
     * - outros campos conforme necessário
     * 
     * PROCESSO:
     * 1. Valida conectividade
     * 2. Serializa dados para JSON
     * 3. Envia via PUT (atualização completa)
     * 4. Valida resposta do servidor
     * 5. Retorna dados atualizados ou confirmação
     * 
     * @param id: ID do utilizador a atualizar
     * @param userData: Dicionário com dados a atualizar
     * @return: Dicionário com dados atualizados ou confirmação
     * @throws: APIError se falha na atualização ou dados inválidos
     */
    func updateUserProfile(id: Int, userData: [String: Any]) async throws -> [String: Any] {
        // Valida conectividade antes de atualizar
        try checkConnection()
        
        // Constrói URL específica para o utilizador
        let url = URL(string: "\(APIService.baseURL)/users/\(id)")!
        var request = URLRequest(url: url)
        request.httpMethod = "PUT"  // Método para atualização completa
        request.setValue("application/json", forHTTPHeaderField: "Content-Type")
        
        // Serializa dados atualizados para JSON
        request.httpBody = try JSONSerialization.data(withJSONObject: userData)
        
        // Executa requisição de atualização
        let (data, response) = try await URLSession.shared.data(for: request)
        
        guard let httpResponse = response as? HTTPURLResponse else {
            throw APIError.networkError("Resposta inválida do servidor")
        }
        
        // Logging do resultado da atualização
        print("Resposta de atualização do perfil - Status: \(httpResponse.statusCode)")
        
        // Verifica se atualização foi bem-sucedida
        if httpResponse.statusCode == 200 {
            if let json = try JSONSerialization.jsonObject(with: data) as? [String: Any] {
                print("Perfil atualizado com sucesso: \(json)")
                return json
            } else {
                throw APIError.serverError("Resposta inválida do servidor")
            }
        } else {
            throw APIError.serverError("Erro do servidor: \(httpResponse.statusCode)")
        }
    }
    
    
    func getOrderDetail(id_encomenda: Int, id_utilizador: Int) async throws -> [String: Any] {
        // Constrói URL com parâmetros de consulta para encomenda específica
        let urlString = "\(APIService.baseURL)/orders/\(id_encomenda)?id_utilizador=\(id_utilizador)"
        print("📡 getOrderDetail: Requisitando URL: \(urlString)")
        
        guard let url = URL(string: urlString) else {
            print("❌ getOrderDetail: URL inválida: \(urlString)")
            throw APIError.invalidURL
        }
        
        // Executa requisição GET
        let (data, response) = try await URLSession.shared.data(from: url)
        
        guard let httpResponse = response as? HTTPURLResponse else {
            print("❌ getOrderDetail: Resposta não é HTTPURLResponse")
            throw APIError.networkError("Resposta inválida do servidor")
        }
        
        print("📊 getOrderDetail: HTTP Status: \(httpResponse.statusCode)")
        
        // Logging dos primeiros 500 caracteres da resposta para debugging
        if let dataString = String(data: data, encoding: .utf8) {
            print("📄 getOrderDetail: Resposta recebida: \(dataString.prefix(500))...")
        }
        
        // Valida resposta HTTP usando função auxiliar
        try handleResponse(httpResponse, data: data)
        
        do {
            // Parse da resposta JSON
            let json = try JSONSerialization.jsonObject(with: data) as? [String: Any]
            guard let result = json else {
                print("❌ getOrderDetail: Resposta não é um dicionário JSON válido")
                throw APIError.decodingError
            }
            
            print("✅ getOrderDetail: Resultado recebido com sucesso")
            
            // Validação específica do array de itens (campo crítico)
            if let itens = result["itens"] as? [[String: Any]] {
                print("📦 getOrderDetail: Quantidade de itens: \(itens.count)")
                if let firstItem = itens.first {
                    print("📦 getOrderDetail: Amostra do primeiro item: \(firstItem)")
                }
            } else {
                print("⚠️ getOrderDetail: Nenhum item encontrado ou campo 'itens' não é array")
            }
            
            return result
        } catch {
            print("❌ getOrderDetail: Erro ao processar JSON: \(error)")
            throw APIError.decodingError
        }
    }
    
    
    func addFavorite(idUtilizador: Int, codigoBase: String) async throws -> (Bool, String) {
        print("🟡 [APIService] addFavorite chamado - UserId: \(idUtilizador), CodigoBase: \(codigoBase)")
        
        // Valida conectividade antes de prosseguir
        try checkConnection()
        
        // Validação e construção da URL
        guard let url = URL(string: "\(APIService.baseURL)/favorites") else {
            print("🔴 [APIService] URL inválida: \(APIService.baseURL)/favorites")
            throw APIError.networkError("URL inválida")
        }
        
        print("🟡 [APIService] URL construída: \(url)")
        
        // Configuração da requisição POST
        var request = URLRequest(url: url)
        request.httpMethod = "POST"  // Adição de novo favorito
        request.setValue("application/json", forHTTPHeaderField: "Content-Type")
        
        // Estrutura dos dados a enviar
        let body = [
            "id_utilizador": idUtilizador,    // Quem está a adicionar
            "codigo_base": codigoBase         // Produto a favoritar
        ] as [String: Any]
        
        print("🟡 [APIService] Body da requisição: \(body)")
        
        // Serializa dados para JSON
        request.httpBody = try JSONSerialization.data(withJSONObject: body)
        
        print("🟡 [APIService] Enviando requisição para adicionar favorito...")
        
        // Executa requisição
        let (data, response) = try await URLSession.shared.data(for: request)
        
        guard let httpResponse = response as? HTTPURLResponse else {
            print("🔴 [APIService] Resposta inválida do servidor")
            throw APIError.networkError("Resposta inválida do servidor")
        }
        
        print("🟡 [APIService] Status code: \(httpResponse.statusCode)")
        
        // Logging da resposta completa
        if let responseString = String(data: data, encoding: .utf8) {
            print("🟡 [APIService] Resposta do servidor: \(responseString)")
        }
        
        // Parse da resposta estruturada
        if let json = try? JSONSerialization.jsonObject(with: data) as? [String: Any],
           let success = json["success"] as? Bool,
           let message = json["message"] as? String {
            print("🟡 [APIService] Resposta parseada - Success: \(success), Message: \(message)")
            return (success, message)
        } else {
            print("🔴 [APIService] Falha ao parsear resposta do servidor")
            throw APIError.serverError("Resposta inesperada do servidor")
        }
    }
    
    
    func removeFavorite(idUtilizador: Int, codigoBase: String) async throws -> (Bool, String) {
        // Valida conectividade antes de prosseguir
        try checkConnection()
        
        // Validação e construção da URL
        guard let url = URL(string: "\(APIService.baseURL)/favorites") else {
            throw APIError.networkError("URL inválida")
        }
        
        // Configuração da requisição DELETE
        var request = URLRequest(url: url)
        request.httpMethod = "DELETE"  // Remoção de favorito
        request.setValue("application/json", forHTTPHeaderField: "Content-Type")
        
        // Estrutura dos dados a enviar (identificação do favorito a remover)
        let body = [
            "id_utilizador": idUtilizador,    // Quem está a remover
            "codigo_base": codigoBase         // Produto a desfavoritar
        ] as [String: Any]
        
        // Serializa dados para JSON
        request.httpBody = try JSONSerialization.data(withJSONObject: body)
        
        // Executa requisição de remoção
        let (data, response) = try await URLSession.shared.data(for: request)
        
        guard let httpResponse = response as? HTTPURLResponse else {
            throw APIError.networkError("Resposta inválida do servidor")
        }
        
        // Parse da resposta estruturada
        if let json = try? JSONSerialization.jsonObject(with: data) as? [String: Any],
           let success = json["success"] as? Bool,
           let message = json["message"] as? String {
            return (success, message)
        } else {
            throw APIError.serverError("Resposta inesperada do servidor")
        }
    }
   
    func getFavorites(idUtilizador: Int) async throws -> [[String: Any]] {
        // Valida conectividade antes de buscar favoritos
        try checkConnection()
        
        // Constrói URL específica para favoritos do utilizador
        guard let url = URL(string: "\(APIService.baseURL)/favorites/\(idUtilizador)") else {
            throw APIError.networkError("URL inválida")
        }
        
        // Executa requisição GET simples
        let (data, response) = try await URLSession.shared.data(from: url)
        
        guard let httpResponse = response as? HTTPURLResponse else {
            throw APIError.networkError("Resposta inválida do servidor")
        }
        
        // Valida resposta usando função auxiliar
        try handleResponse(httpResponse, data: data)
        
        // Tenta converter resposta para array de favoritos
        if let favorites = try? JSONSerialization.jsonObject(with: data) as? [[String: Any]] {
            return favorites
        } else {
            throw APIError.serverError("Resposta inesperada do servidor")
        }
    }
    
    
    func checkIsFavorite(idUtilizador: Int, codigoBase: String) async throws -> Bool {
        // Valida conectividade antes de verificar
        try checkConnection()
        
        // Constrói URL específica para verificação de favorito
        guard let url = URL(string: "\(APIService.baseURL)/favorites/\(idUtilizador)/check/\(codigoBase)") else {
            throw APIError.networkError("URL inválida")
        }
        
        // Executa requisição GET otimizada
        let (data, response) = try await URLSession.shared.data(from: url)
        
        guard let httpResponse = response as? HTTPURLResponse else {
            throw APIError.networkError("Resposta inválida do servidor")
        }
        
        // Valida resposta usando função auxiliar
        try handleResponse(httpResponse, data: data)
        
        // Extrai valor booleano da resposta
        if let json = try? JSONSerialization.jsonObject(with: data) as? [String: Any],
           let isFavorite = json["isFavorite"] as? Bool {
            return isFavorite
        } else {
            // Fallback seguro - assume que não é favorito se não conseguir determinar
            return false
        }
    }
    
    
    
    deinit {
        monitor.cancel()
    }
    
    /**
     * Função auxiliar para compatibilidade com padrão de callback
     * 
     * Fornece uma interface de callback tradicional para código legacy
     * que ainda não usa async/await. Internamente usa a função moderna
     * register() e converte o resultado para callback.
     * 
     * @param nome: Nome do utilizador
     * @param email: Email do utilizador
     * @param password: Password do utilizador
     * @param localEnvio: Endereço opcional
     * @param completion: Callback executado com resultado (sucesso, mensagem)
     */
    func registerUser(nome: String, email: String, password: String, localEnvio: String?, completion: @escaping (Bool, String) -> Void) {
        Task {
            do {
                let result = try await register(nome: nome, email: email, palavra_passe: password, local_envio: localEnvio)
                if let success = result["success"] as? Bool, let message = result["message"] as? String {
                    completion(success, message)
                } else {
                    completion(true, "Conta criada com sucesso")
                }
            } catch {
                completion(false, "Erro ao criar conta: \(error.localizedDescription)")
            }
        }
    }
    
    // MARK: - Reviews de Produtos (API Real)
    
    /**
     * Busca reviews de um produto específico da API
     */
    func fetchProductReviews(codigoBase: String) async throws -> [ProductReview] {
        guard isConnected else {
            throw APIError.networkError("Sem conexão à internet")
        }
        
        guard let url = URL(string: "\(APIService.baseURL)/products/\(codigoBase)/reviews") else {
            throw APIError.invalidURL
        }
        
        let (data, response) = try await URLSession.shared.data(from: url)
        
        guard let httpResponse = response as? HTTPURLResponse,
              httpResponse.statusCode == 200 else {
            throw APIError.invalidResponse
        }
        
        return try JSONDecoder().decode([ProductReview].self, from: data)
    }
    
    /**
     * Submete nova review de produto
     */
    func submitProductReview(idEncomenda: Int, codigoBase: String, idUtilizador: Int, classificacao: Int, comentario: String) async throws {
        guard isConnected else {
            throw APIError.networkError("Sem conexão à internet")
        }
        
        guard let url = URL(string: "\(APIService.baseURL)/products/reviews") else {
            throw APIError.invalidURL
        }
        
        var request = URLRequest(url: url)
        request.httpMethod = "POST"
        request.setValue("application/json", forHTTPHeaderField: "Content-Type")
        
        let reviewData = [
            "id_encomenda": idEncomenda,
            "codigo_base": codigoBase,
            "id_utilizador": idUtilizador,
            "classificacao": classificacao,
            "comentario": comentario
        ] as [String : Any]
        
        request.httpBody = try JSONSerialization.data(withJSONObject: reviewData)
        
        let (_, response) = try await URLSession.shared.data(for: request)
        
        guard let httpResponse = response as? HTTPURLResponse else {
            throw APIError.invalidResponse
        }
        
        if httpResponse.statusCode == 409 {
            throw APIError.serverError("Review já existe para este produto")
        } else if httpResponse.statusCode != 201 {
            throw APIError.serverError("Erro ao submeter review")
        }
    }
    
    /**
     * Verifica se utilizador já fez review de um produto numa encomenda
     */
    func hasProductReview(idEncomenda: Int, codigoBase: String, idUtilizador: Int) async throws -> Bool {
        guard isConnected else {
            throw APIError.networkError("Sem conexão à internet")
        }
        
        let queryParams = "id_encomenda=\(idEncomenda)&codigo_base=\(codigoBase)&id_utilizador=\(idUtilizador)"
        guard let url = URL(string: "\(APIService.baseURL)/products/reviews/check?\(queryParams)") else {
            throw APIError.invalidURL
        }
        
        let (data, response) = try await URLSession.shared.data(from: url)
        
        guard let httpResponse = response as? HTTPURLResponse,
              httpResponse.statusCode == 200 else {
            throw APIError.invalidResponse
        }
        
        let result = try JSONSerialization.jsonObject(with: data) as? [String: Any]
        return result?["hasReview"] as? Bool ?? false
    }
    
    // MARK: - Reviews de Encomendas (API Real)
    
    /**
     * Busca reviews de uma encomenda específica da API
     */
    func fetchOrderReviews(idEncomenda: Int) async throws -> [OrderReview] {
        guard isConnected else {
            throw APIError.networkError("Sem conexão à internet")
        }
        
        guard let url = URL(string: "\(APIService.baseURL)/orders/\(idEncomenda)/reviews") else {
            throw APIError.invalidURL
        }
        
        let (data, response) = try await URLSession.shared.data(from: url)
        
        guard let httpResponse = response as? HTTPURLResponse,
              httpResponse.statusCode == 200 else {
            throw APIError.invalidResponse
        }
        
        return try JSONDecoder().decode([OrderReview].self, from: data)
    }
    
    /**
     * Submete nova review de encomenda
     */
    func submitOrderReview(idEncomenda: Int, idUtilizador: Int, classificacao: Int, comentario: String) async throws {
        guard isConnected else {
            throw APIError.networkError("Sem conexão à internet")
        }
        
        guard let url = URL(string: "\(APIService.baseURL)/orders/reviews") else {
            throw APIError.invalidURL
        }
        
        var request = URLRequest(url: url)
        request.httpMethod = "POST"
        request.setValue("application/json", forHTTPHeaderField: "Content-Type")
        
        let reviewData = [
            "id_encomenda": idEncomenda,
            "id_utilizador": idUtilizador,
            "classificacao": classificacao,
            "comentario": comentario
        ] as [String : Any]
        
        request.httpBody = try JSONSerialization.data(withJSONObject: reviewData)
        
        let (_, response) = try await URLSession.shared.data(for: request)
        
        guard let httpResponse = response as? HTTPURLResponse else {
            throw APIError.invalidResponse
        }
        
        if httpResponse.statusCode == 409 {
            throw APIError.serverError("Review já existe para esta encomenda")
        } else if httpResponse.statusCode != 201 {
            throw APIError.serverError("Erro ao submeter review")
        }
    }
    
    /**
     * Verifica se utilizador já fez review de uma encomenda
     */
    func hasOrderReview(idEncomenda: Int, idUtilizador: Int) async throws -> Bool {
        guard isConnected else {
            throw APIError.networkError("Sem conexão à internet")
        }
        
        let queryParams = "id_encomenda=\(idEncomenda)&id_utilizador=\(idUtilizador)"
        guard let url = URL(string: "\(APIService.baseURL)/orders/reviews/check?\(queryParams)") else {
            throw APIError.invalidURL
        }
        
        let (data, response) = try await URLSession.shared.data(from: url)
        
        guard let httpResponse = response as? HTTPURLResponse,
              httpResponse.statusCode == 200 else {
            throw APIError.invalidResponse
        }
        
        let result = try JSONSerialization.jsonObject(with: data) as? [String: Any]
        return result?["hasReview"] as? Bool ?? false
    }
    
}