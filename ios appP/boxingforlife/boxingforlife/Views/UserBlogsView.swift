// Import do framework SwiftUI - necessário para usar todos os componentes de UI do SwiftUI
import SwiftUI


struct UserBlogsView: View {
   
    @EnvironmentObject private var authManager: AuthManager
    
   
    @State private var userBlogs: [[String: Any]] = []
    
   
    @State private var isLoading = false
    
    
    @State private var errorMessage = ""
    
   
    @State private var showError = false
    
   
    @State private var showingCreateBlog = false
    
    @State private var hasLoadedInitially = false
    
    
    private let primaryColor = Color(red: 0.8, green: 0.2, blue: 0.2) // Vermelho boxe
    
    
    var body: some View {
        // NavigationView fornece contexto de navegação e barra de título
        NavigationView {
            // Group permite agrupar vistas condicionalmente sem afetar o layout
            Group {
                
                if isLoading {
                    // ProgressView com texto personalizado mostra carregamento
                    ProgressView("Carregando blogs...")
                        .frame(maxWidth: .infinity, maxHeight: .infinity) // Ocupa toda a tela disponível
                }
                // Estado 2: Nenhum blog encontrado (lista vazia)
                else if userBlogs.isEmpty {
                    // Vista personalizada para estado vazio
                    emptyStateView
                }
                // Estado 3: Blogs carregados com sucesso
                else {
                    // Vista que mostra a lista de blogs
                    blogsList
                }
            }
            
            .navigationTitle("Meus Blogs")
            
            // Configura botões e elementos na barra de navegação
            .toolbar {
                // ToolbarItem permite adicionar items específicos na toolbar
                // placement: .navigationBarTrailing coloca o item no lado direito da barra
                ToolbarItem(placement: .navigationBarTrailing) {
                    // Botão para criar novo blog
                    Button(action: {
                        // Define showingCreateBlog como true para apresentar a folha modal
                        showingCreateBlog = true
                    }) {
                        // Ícone de "plus" do sistema SF Symbols
                        Image(systemName: "plus")
                    }
                }
            }
            
            
            .refreshable {
                // Recarrega os blogs do utilizador quando ativado
                await loadUserBlogs()
            }
            
            
            .alert("Erro", isPresented: $showError) {
                // Botão único "OK" com role cancel (estilo padrão de cancelamento)
                Button("OK", role: .cancel) {}
            } message: {
                // Corpo da mensagem do alerta (usa a variável errorMessage)
                Text(errorMessage)
            }
            
            // Folha modal que aparece quando showingCreateBlog é true
            .sheet(isPresented: $showingCreateBlog) {
                // Apresenta a vista CreateBlogPostView para criar novo blog
                CreateBlogPostView()
            }
            
            
            .onAppear {
                // Só carrega se ainda não carregou inicialmente
                if !hasLoadedInitially {
                    hasLoadedInitially = true
                    Task {
                        await loadUserBlogs()
                    }
                }
            }
        }
    }
    
    
    private var emptyStateView: some View {
        // VStack organiza elementos verticalmente com espaçamento de 20 pontos
        VStack(spacing: 20) {
            
            
            Image(systemName: "doc.text")
                .font(.system(size: 60)) // Tamanho grande para destaque visual
                .foregroundColor(.gray) // Cor cinzenta para suavizar o impacto
            
            
            Text("Ainda não criou nenhum blog")
                .font(.title2) // Estilo de fonte para subtítulos importantes
                .fontWeight(.semibold) // Peso semi-negrito para dar destaque
            
            
            Text("Comece a partilhar as suas experiências e conhecimentos sobre boxe!")
                .font(.body) // Estilo de fonte normal para corpo do texto
                .foregroundColor(.gray) // Cor cinzenta para diminuir importância visual
                .multilineTextAlignment(.center) // Alinhamento central para múltiplas linhas
                .padding(.horizontal) // Padding horizontal para evitar texto muito próximo das bordas
            
            
            Button(action: {
                // Ativa a folha modal para criar novo blog
                showingCreateBlog = true
            }) {
                Text("Criar Primeiro Blog")
                    .foregroundColor(.white) // Texto branco para contraste
                    .padding() // Padding interno para aumentar área tocável
                    .background(primaryColor) // Fundo com cor primária (vermelho boxe)
                    .cornerRadius(10) // Bordas arredondadas para design moderno
            }
        }
        .padding() // Padding externo ao redor de todo o VStack
    }
    
    
    private var blogsList: some View {
        // List cria uma lista scrollable nativa do iOS
        List {
            // ForEach itera sobre os índices do array userBlogs
            // Usa índices em vez de objetos para permitir modificações (como eliminação)
            ForEach(userBlogs.indices, id: \.self) { index in
                // Obtém o blog atual do array usando o índice
                let blog = userBlogs[index]
                
                // Componente personalizado que renderiza uma linha de blog
                BlogRowView(blog: blog) {
                    // MARK: - Ação de Edição (onEdit closure)
                    // Ação para editar blog (funcionalidade em desenvolvimento)
                    // TODO: Implementar navegação para vista de edição
                    print("Editar blog ID: \(blog["id_post"] ?? "")")
                } onDelete: {
                    // MARK: - Ação de Eliminação (onDelete closure)
                    // Chama função para eliminar o blog no índice atual
                    deleteBlog(at: index)
                }
            }
        }
        // PlainListStyle remove separadores e estilo padrão para aparência mais limpa
        .listStyle(PlainListStyle())
    }
    
    
    @MainActor
    private func loadUserBlogs() async {
        guard let user = authManager.currentUser else { return }
        
        // Evita recarregamentos múltiplos
        if isLoading { return }
        
        isLoading = true
        
        do {
            // Busca todos os blogs do servidor via APIService
            let allBlogs = try await APIService.shared.getBlogPosts()
            
            // Filtra blogs para mostrar apenas os do utilizador atual
            userBlogs = allBlogs.filter { blog in
                if let authorId = blog["id_utilizador"] as? Int {
                    return authorId == user.id
                }
                return false
            }
            // Ordena por data de publicação (mais recente primeiro)
            .sorted { blog1, blog2 in
                let date1 = blog1["data_publicacao"] as? String ?? ""
                let date2 = blog2["data_publicacao"] as? String ?? ""
                return date1 > date2
            }
        }
        catch {
            errorMessage = "Erro ao carregar blogs: \(error.localizedDescription)"
            showError = true
        }
        
        isLoading = false
    }
    
    
    private func deleteBlog(at index: Int) {
        // MARK: - Validação de Utilizador e Dados
        
        // Verifica se há um utilizador logado
        guard let user = authManager.currentUser else { return }
        
        // Obtém o blog a ser eliminado usando o índice
        let blog = userBlogs[index]
        
        // Extrai o ID do blog do dicionário e valida
        guard let blogId = blog["id_post"] as? Int else {
            // Se não conseguir obter ID válido, mostra erro e retorna
            errorMessage = "Erro: ID do blog inválido"
            showError = true
            return
        }
        
       
        Task {
            // Bloco do-catch para tratamento de erros
            do {
                // Chama APIService para eliminar o blog no servidor
                // Passa ID do blog e ID do utilizador para validação de permissões
                let _ = try await APIService.shared.deleteBlogPost(blogId: blogId, userId: user.id)
                
                // MARK: - Atualização da UI na Main Thread
                
                // MainActor.run garante que as mudanças na UI acontecem na thread principal
                await MainActor.run {
                    // Remove o blog do array local (atualiza a lista instantaneamente)
                    userBlogs.remove(at: index)
                }
            }
            catch {
                // Em caso de erro, atualiza a UI na main thread
                await MainActor.run {
                    // Define mensagem de erro personalizada
                    errorMessage = "Erro ao eliminar blog: \(error.localizedDescription)"
                    // Ativa exibição do alerta de erro
                    showError = true
                }
            }
        }
    }
}


struct BlogRowView: View {
    // MARK: - Propriedades
    
    // Dicionário contendo os dados do blog (título, resumo, data, etc.)
    let blog: [String: Any]
    
    // Closure executada quando o utilizador escolhe editar o blog
    let onEdit: () -> Void
    
    // Closure executada quando o utilizador escolhe eliminar o blog
    let onDelete: () -> Void
    
    
    var body: some View {
        // VStack organiza elementos verticalmente com espaçamento de 8 pontos
        VStack(alignment: .leading, spacing: 8) {
            
            // MARK: - Cabeçalho (Título + Menu de Ações)
            // HStack organiza título e menu horizontalmente
            HStack {
                // MARK: - Título do Blog
                // Extrai título do dicionário, usa fallback se não existir
                Text(blog["titulo"] as? String ?? "Sem título")
                    .font(.headline) // Estilo de fonte em destaque
                    .lineLimit(2) // Limita a 2 linhas para evitar quebra excessiva
                
                // Spacer empurra o menu para o lado direito
                Spacer()
                
                // MARK: - Menu de Opções
                // Menu contextual com opções de edição e eliminação
                Menu {
                    // Opção para editar blog
                    Button("Editar", action: onEdit)
                    // Opção para eliminar blog (role: .destructive deixa texto vermelho)
                    Button("Eliminar", role: .destructive, action: onDelete)
                } label: {
                    // Ícone de três pontos para indicar menu
                    Image(systemName: "ellipsis")
                        .foregroundColor(.gray) // Cor cinzenta para diminuir destaque
                }
            }
            
            
            if let resumo = blog["resumo"] as? String, !resumo.isEmpty {
                Text(resumo)
                    .font(.subheadline) // Fonte menor que o título
                    .foregroundColor(.gray) // Cor cinzenta para informação secundária
                    .lineLimit(3) // Limita a 3 linhas para não ocupar muito espaço
            }
            
           
            HStack {
                
                if let dataPublicacao = blog["data_publicacao"] as? String {
                    Text(formatDate(dataPublicacao))
                        .font(.caption) // Fonte pequena para informação terciária
                        .foregroundColor(.gray) // Cor cinzenta para diminuir importância
                }
                
                // Spacer empurra o status para o lado direito
                Spacer()
                
                
                Text("Publicado")
                    .font(.caption) // Fonte pequena
                    .padding(.horizontal, 8) // Padding horizontal interno
                    .padding(.vertical, 2) // Padding vertical interno
                    .background(Color.green.opacity(0.2)) // Fundo verde claro
                    .foregroundColor(.green) // Texto verde
                    .cornerRadius(4) // Bordas ligeiramente arredondadas
            }
        }
        .padding(.vertical, 4) // Padding vertical externo para espaçamento entre linhas
    }
    
    
    private func formatDate(_ dateString: String) -> String {
        // Cria um ISO8601DateFormatter para parsing de datas ISO
        let formatter = ISO8601DateFormatter()
        // Define opções para parsing: data/hora da internet + frações de segundo
        formatter.formatOptions = [.withInternetDateTime, .withFractionalSeconds]
        
        // Tenta fazer o parsing da string para um objeto Date
        if let date = formatter.date(from: dateString) {
            // Se successful, cria um DateFormatter para o formato de saída
            let displayFormatter = DateFormatter()
            // Define estilo médio para data (ex: "15 jan 2024")
            displayFormatter.dateStyle = .medium
            // Define estilo curto para hora (ex: "14:30")
            displayFormatter.timeStyle = .short
            // Define locale português para nomes de meses em português
            displayFormatter.locale = Locale(identifier: "pt_PT")
            // Retorna a data formatada como string local
            return displayFormatter.string(from: date)
        }
        
        // Se o parsing falhar, retorna a string original
        return dateString
    }
}


#Preview {
    UserBlogsView()
        .environmentObject(AuthManager()) // Fornece AuthManager mock para preview
}
