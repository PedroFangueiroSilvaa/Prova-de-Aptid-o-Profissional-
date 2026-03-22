

import Foundation // Para UserDefaults, Task, etc.

class CartManager: ObservableObject {
    
   
    static let shared = CartManager()

    
    @Published var cartItems: [CartItem] = []
    
    
    private(set) var sessionId: String
    
    
    private let apiService = APIService.shared
    
    
    var isLoggedIn: Bool {
        // UserDefaults.bool(): retorna false se chave não existe
        return UserDefaults.standard.bool(forKey: "isLoggedIn")
    }
    
    
    var id_utilizador: Int {
        return UserDefaults.standard.integer(forKey: "userId")
    }
    
    
    init() {
        // ========================================
        // CONFIGURAÇÃO DO SESSION ID PARA UTILIZADORES ANÓNIMOS
        // ========================================
        // Tenta obter sessionId existente dos UserDefaults
        // Se não existir, significa que app nunca foi iniciada ou foi reinstalada
        if let existingSessionId = UserDefaults.standard.string(forKey: "cartSessionId") {
            // SessionId encontrado: reutiliza sessão existente
            // Permite continuidade do carrinho entre execuções da app
            self.sessionId = existingSessionId
        } else {
            // SessionId não encontrado: erro crítico de configuração
            // Este valor deve ser criado no AppDelegate ou ContentView
            // fatalError: termina app imediatamente (só para erros irrecuperáveis)
            fatalError("session_id não encontrado. Certifique-se de que ele é configurado corretamente no dispositivo.")
        }
        
        
        Task {
            // await: aguarda operação assíncrona terminar
            await refreshCart()
        }
    }
    
   
    var total: Double {
        // reduce(0): parte de 0 e acumula valores
        // $0: acumulador (total parcial)
        // $1: item atual do array
        // { }: closure que define como acumular
        return cartItems.reduce(0) { accumulator, currentItem in
            // Calcula subtotal do item atual: preço × quantidade
            let itemSubtotal = currentItem.preco_unitario * Double(currentItem.quantidade)
            // Adiciona ao total acumulado
            return accumulator + itemSubtotal
        }
        // VERSÃO COMPACTA: cartItems.reduce(0) { $0 + ($1.preco_unitario * Double($1.quantidade)) }
    }
    
    
    func refreshCart() async {
        // Chama método interno de busca de dados
        await getCartItems()
    }
    
    
    func getCartItems() async {
        do {
            
            let items: [[String: Any]]
            
            
            if isLoggedIn {
                
                items = try await apiService.getCart(id_utilizador: id_utilizador)
            } else {
                
                items = try await apiService.getCartBySession(sessionId: sessionId)
            }
            
            // Atualiza a UI na thread principal
            await MainActor.run {
                self.cartItems = items.compactMap { item in
                    guard let id = item["id_carrinho"] as? Int,
                          let sku = item["sku"] as? String,
                          let quantidade = item["quantidade"] as? Int,
                          let preco_unitario = item["preco_unitario"] as? Double,
                          let nome = item["nome"] as? String,
                          let imagem = item["imagem"] as? String,
                          let tamanho = item["tamanho"] as? String,
                          let cor = item["cor"] as? String else {
                        return nil
                    }
                    
                    return CartItem(
                        id: id,
                        sku: sku,
                        quantidade: quantidade,
                        preco_unitario: preco_unitario,
                        nome: nome,
                        imagem: imagem,
                        tamanho: tamanho,
                        cor: cor
                    )
                }
            }
        } catch {
            print("Erro ao buscar itens do carrinho: \(error)")
        }
    }
    
    func addToCart(sku: String, quantidade: Int, preco_unitario: Double, nome: String, imagem: String, tamanho: String, cor: String) async throws {
        print("Adicionando ao carrinho - SKU: \(sku), Quantidade: \(quantidade)")
        
        let userId = UserDefaults.standard.integer(forKey: "userId")
        let isLoggedIn = UserDefaults.standard.bool(forKey: "isLoggedIn")
        
        print("Estado do usuário - Logado: \(isLoggedIn), UserID: \(userId)")
        
        do {
            // Se não estiver logado, usa apenas o session_id
            if (!isLoggedIn) {
                print("Usuário não logado - usando session_id: \(sessionId)")
                _ = try await APIService.shared.addToCart(
                    id_utilizador: nil,
                    session_id: sessionId,
                    sku: sku,
                    quantidade: quantidade
                )
            } else {
                // Se estiver logado, usa apenas o id_utilizador
                print("Usuário logado - usando id_utilizador: \(userId)")
                _ = try await APIService.shared.addToCart(
                    id_utilizador: userId,
                    session_id: nil,
                    sku: sku,
                    quantidade: quantidade
                )
            }
            
            // Atualizar o carrinho após adicionar o item
            await getCartItems()
            print("Carrinho atualizado após adicionar item")
        } catch {
            print("Erro ao adicionar ao carrinho: \(error)")
            throw error
        }
    }
    
    func removeFromCart(item: CartItem) {
        cartItems.removeAll { $0.id == item.id }
        print("Item removido do carrinho. Total de itens: \(cartItems.count)")
    }
    
    func clearCart() {
        cartItems.removeAll()
        print("Carrinho limpo")
    }
    
    func handleLogout() {
        print("Limpando carrinho ao fazer logout")
        clearCart()
    }
    
    func transferCartOnLogin(userId: Int) async {
        do {
            let sessionItems = try await apiService.getCartBySession(sessionId: sessionId)
            
            if !sessionItems.isEmpty {
                try await apiService.transferCart(fromSessionId: sessionId, toUserId: userId)
                await getCartItems()
            }
        } catch {
            print("Erro ao transferir carrinho: \(error)")
        }
    }
}