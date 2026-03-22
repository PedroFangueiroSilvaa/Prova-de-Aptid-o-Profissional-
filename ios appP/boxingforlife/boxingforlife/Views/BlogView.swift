/**
 * BlogView.swift - Tela Principal do Blog
 * 
 * FUNCIONALIDADE PRINCIPAL:
 * Interface para visualização de artigos de blog da comunidade Boxing for Life,
 * com capacidade de criação de novos posts por utilizadores autenticados.
 * 
 * RECURSOS IMPLEMENTADOS:
 * ✅ Listagem de todos os posts de blog públicos
 * ✅ Navegação para detalhes de posts individuais
 * ✅ Botão flutuante para criação de novos posts
 * ✅ Verificação de autenticação para criação
 * ✅ Pull-to-refresh para atualização manual
 * ✅ Estados de loading e tratamento de erros
 * ✅ Layout otimizado com LazyVStack
 * ✅ Integração com ViewModel (MVVM)
 * ✅ Alertas informativos para utilizadores
 * 
 * FLUXO DE DADOS:
 * BlogView -> BlogViewModel -> APIService -> Servidor
 * 
 * ARQUITETURA:
 * - MVVM pattern com BlogViewModel
 * - StateObject para lifecycle management
 * - EnvironmentObject para AuthManager
 * - Sheet navigation para criação e detalhes
 * 
 * DEPENDÊNCIAS:
 * - SwiftUI: Framework de interface
 * - BlogViewModel: Gestão de dados e lógica
 * - AuthManager: Verificação de autenticação
 * - BlogPostCard: Componente de post individual
 * - CreateBlogPostView: Tela de criação
 */

import SwiftUI

struct BlogPost: Identifiable {
    
    let id: Int
    
    
    let dictionary: [String: Any]
    
   
    init(dictionary: [String: Any]) {
        self.id = dictionary["id_post"] as? Int ?? 0
        self.dictionary = dictionary
    }
}


struct BlogView: View {
    
    @StateObject private var viewModel = BlogViewModel()
    @State private var selectedPost: BlogPost?
    @State private var showCreatePostSheet = false
    @State private var showError = false
    @State private var errorMessage = ""
    @State private var showLoginAlert = false
    @EnvironmentObject private var authManager: AuthManager
    
    var body: some View {
        NavigationView {
            ZStack {
                if viewModel.isLoading {
                    ProgressView()
                } else {
                    ScrollView {
                        LazyVStack(spacing: 16) {
                            ForEach(viewModel.blogPosts) { post in
                                BlogPostCard(post: post)
                                    .onTapGesture {
                                        selectedPost = post
                                    }
                            }
                        }
                        .padding()
                    }
                    .refreshable {
                        await viewModel.loadPosts()
                    }
                    
                    VStack {
                        Spacer()
                        HStack {
                            Spacer()
                            Button(action: {
                               
                                let userId = UserDefaults.standard.integer(forKey: "userId")
                                let isLoggedIn = userId > 0
                                
                                if isLoggedIn {
                                    showCreatePostSheet = true
                                } else {
                                    showLoginAlert = true
                                }
                            }) {
                                
                                Image(systemName: "plus")
                                    .font(.title)
                                    .foregroundColor(.white)
                                    .frame(width: 60, height: 60)
                                    .background(Color(red: 0.8, green: 0.2, blue: 0.2))
                                    .clipShape(Circle())
                                    .shadow(radius: 5)
                            }
                            .padding(.trailing, 20)
                            .padding(.bottom, 20)
                        }
                    }
                }
            }
            .navigationTitle("Blog")
            
            // Modal para mostrar detalhes do post selecionado
            .sheet(item: $selectedPost) { post in
                BlogPostDetailView(post: post)
                    .environmentObject(authManager)
            }
            
            // Modal para criar novo post
            .sheet(isPresented: $showCreatePostSheet) {
                CreateBlogPostView()
                    .environmentObject(authManager)
            }
            
            // Alert para mostrar erros
            .alert("Erro", isPresented: $showError) {
                Button("OK", role: .cancel) {}
            } message: {
                Text(errorMessage)
            }
            
            // Alert quando utilizador não logado tenta criar post
            .alert("Login Necessário", isPresented: $showLoginAlert) {
                Button("OK", role: .cancel) {}
            } message: {
                Text("Você precisa estar logado para criar um blog.")
            }
            
            // Carrega posts quando a tela aparece
            .task {
                await viewModel.loadPosts()
            }
        }
    }
}

// Classe ViewModel que gere a lógica de negócio do blog
// ObservableObject: permite que as Views sejam notificadas quando há mudanças
class BlogViewModel: ObservableObject {
    // Lista de posts de blog (observável pelas Views)
    @Published private(set) var blogPosts: [BlogPost] = []
    @Published var isLoading = false
    
    @MainActor
    func loadPosts() async {
        guard !isLoading else { return }
        
        isLoading = true
        do {
            let posts = try await APIService.shared.getBlogPosts()
            blogPosts = posts.map { BlogPost(dictionary: $0) }
        } catch {
            print("Error loading blog posts: \(error)")
        }
        isLoading = false
    }
}

struct BlogPostCard: View {
    let post: BlogPost
    
    var body: some View {
        VStack(alignment: .leading, spacing: 8) {
            if let imageUrl = post.dictionary["imagem"] as? String {
                // Processando a URL da imagem corretamente
                let processedUrl = APIService.shared.processImageURL(imageUrl)
                AsyncImage(url: URL(string: processedUrl)) { image in
                    image
                        .resizable()
                        .aspectRatio(contentMode: .fill)
                } placeholder: {
                    Rectangle()
                        .foregroundColor(.gray)
                }
                .frame(height: 200)
                .clipped()
            }
            
            VStack(alignment: .leading, spacing: 4) {
                Text(post.dictionary["titulo"] as? String ?? "")
                    .font(.headline)
                
                Text(post.dictionary["conteudo"] as? String ?? "")
                    .font(.subheadline)
                    .foregroundColor(.gray)
                    .lineLimit(3)
                
                if let data = post.dictionary["data_publicacao"] as? String {
                    Text(formatDate(data))
                        .font(.caption)
                        .foregroundColor(.gray)
                }
            }
            .padding(.horizontal)
            .padding(.bottom)
        }
        .background(Color(.systemBackground))
        .cornerRadius(10)
        .shadow(radius: 5)
    }
    
    private func formatDate(_ dateString: String) -> String {
        let formatter = DateFormatter()
        formatter.dateFormat = "yyyy-MM-dd'T'HH:mm:ss.SSSZ"
        
        guard let date = formatter.date(from: dateString) else {
            return dateString
        }
        
        formatter.dateStyle = .medium
        formatter.timeStyle = .short
        return formatter.string(from: date)
    }
}

struct BlogPostDetailView: View {
    let post: BlogPost
    @Environment(\.dismiss) private var dismiss
    @EnvironmentObject private var authManager: AuthManager
    @State private var comments: [BlogComment] = []
    @State private var isLoadingComments = false
    @State private var newComment: String = ""
    @State private var isSubmittingComment = false
    @State private var showError = false
    @State private var errorMessage = ""
    @State private var showLoginAlert = false
    
    var body: some View {
        NavigationView {
            ScrollView {
                VStack(alignment: .leading, spacing: 16) {
                    if let imageUrl = post.dictionary["imagem"] as? String {
                        // Processando a URL da imagem corretamente
                        let processedUrl = APIService.shared.processImageURL(imageUrl)
                        AsyncImage(url: URL(string: processedUrl)) { image in
                            image
                                .resizable()
                                .aspectRatio(contentMode: .fill)
                        } placeholder: {
                            Rectangle()
                                .foregroundColor(.gray)
                        }
                        .frame(height: 300)
                        .clipped()
                    }
                    
                    VStack(alignment: .leading, spacing: 8) {
                        Text(post.dictionary["titulo"] as? String ?? "")
                            .font(.title)
                            .fontWeight(.bold)
                        
                        if let data = post.dictionary["data_publicacao"] as? String {
                            Text(formatDate(data))
                                .font(.subheadline)
                                .foregroundColor(.gray)
                        }
                        
                        Text(post.dictionary["conteudo"] as? String ?? "")
                            .font(.body)
                            .padding(.top)
                    }
                    .padding()
                    
                    // Seção de comentários
                    VStack(alignment: .leading, spacing: 12) {
                        HStack {
                            Text("Comentários")
                                .font(.headline)
                            Spacer()
                            Text("\(comments.count)")
                                .foregroundColor(.gray)
                        }
                        .padding(.horizontal)
                        
                        if isLoadingComments {
                            ProgressView()
                                .frame(maxWidth: .infinity)
                                .padding()
                        } else if comments.isEmpty {
                            Text("Nenhum comentário ainda. Seja o primeiro a comentar!")
                                .foregroundColor(.gray)
                                .frame(maxWidth: .infinity, alignment: .center)
                                .padding()
                        } else {
                            ForEach(comments) { comment in
                                CommentView(comment: comment)
                            }
                        }
                        
                        // Campo para adicionar comentário
                        VStack(spacing: 8) {
                            HStack {
                                TextField("Escreva um comentário...", text: $newComment)
                                    .padding(10)
                                    .background(Color.gray.opacity(0.1))
                                    .cornerRadius(8)
                                
                                Button(action: {
                                    Task {
                                        await submitComment()
                                    }
                                }) {
                                    if isSubmittingComment {
                                        ProgressView()
                                            .progressViewStyle(CircularProgressViewStyle())
                                    } else {
                                        Image(systemName: "paperplane.fill")
                                            .foregroundColor(.blue)
                                    }
                                }
                                .disabled(newComment.trimmingCharacters(in: .whitespacesAndNewlines).isEmpty || isSubmittingComment)
                                .padding(10)
                            }
                            
                            if !authManager.isLoggedIn {
                                Text("Faça login para comentar")
                                    .font(.caption)
                                    .foregroundColor(.gray)
                            }
                        }
                        .padding()
                    }
                }
            }
            .navigationBarTitleDisplayMode(.inline)
            .navigationBarItems(trailing: Button("Fechar") {
                dismiss()
            })
            .task {
                await loadComments()
            }
            .alert("Erro", isPresented: $showError) {
                Button("OK", role: .cancel) {}
            } message: {
                Text(errorMessage)
            }
            .alert("Login Necessário", isPresented: $showLoginAlert) {
                Button("Fazer Login") {
                    // Aqui você pode adicionar lógica para navegar para a tela de login
                    dismiss()
                }
                Button("Cancelar", role: .cancel) {}
            } message: {
                Text("Você precisa estar logado para comentar.")
            }
        }
    }
    
    private func formatDate(_ dateString: String) -> String {
        let formatter = DateFormatter()
        formatter.dateFormat = "yyyy-MM-dd'T'HH:mm:ss.SSSZ"
        
        guard let date = formatter.date(from: dateString) else {
            return dateString
        }
        
        formatter.dateStyle = .medium
        formatter.timeStyle = .short
        return formatter.string(from: date)
    }
    
    private func loadComments() async {
        isLoadingComments = true
        
        do {
            let commentsData = try await APIService.shared.getBlogComments(postId: post.id)
            comments = commentsData.map { BlogComment(dictionary: $0) }
        } catch {
            print("Erro ao carregar comentários: \(error)")
            showError = true
            errorMessage = "Não foi possível carregar os comentários: \(error.localizedDescription)"
        }
        
        isLoadingComments = false
    }
    
    private func submitComment() async {
        // Verificar o login usando UserDefaults ao invés de authManager
        let userId = UserDefaults.standard.integer(forKey: "userId")
        let isLoggedIn = userId > 0
        
        guard isLoggedIn else {
            showLoginAlert = true
            return
        }
        
        guard userId > 0 else {
            showError = true
            errorMessage = "Erro ao identificar o usuário."
            return
        }
        
        let commentText = newComment.trimmingCharacters(in: .whitespacesAndNewlines)
        guard !commentText.isEmpty else { return }
        
        isSubmittingComment = true
        
        do {
            _ = try await APIService.shared.addBlogComment(
                postId: post.id,
                userId: userId,
                comment: commentText
            )
            
            // Limpa o campo de comentário e recarrega os comentários
            newComment = ""
            await loadComments()
        } catch {
            showError = true
            errorMessage = "Erro ao enviar comentário: \(error.localizedDescription)"
        }
        
        isSubmittingComment = false
    }
}

struct CommentView: View {
    let comment: BlogComment
    
    var body: some View {
        VStack(alignment: .leading, spacing: 4) {
            HStack {
                Text(comment.userName)
                    .font(.subheadline)
                    .fontWeight(.bold)
                
                Spacer()
                
                Text(comment.formattedDate)
                    .font(.caption)
                    .foregroundColor(.gray)
            }
            
            Text(comment.content)
                .font(.body)
        }
        .padding()
        .background(Color.gray.opacity(0.1))
        .cornerRadius(8)
        .padding(.horizontal)
    }
}

#Preview {
    BlogView()
}