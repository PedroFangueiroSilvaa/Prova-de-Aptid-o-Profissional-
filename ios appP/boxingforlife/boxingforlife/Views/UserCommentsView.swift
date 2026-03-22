
import SwiftUI


struct UserCommentsView: View {
    
    @EnvironmentObject private var authManager: AuthManager
    
    
    @State private var userComments: [[String: Any]] = []
    @State private var isLoading = false
    @State private var errorMessage = ""
    @State private var showError = false
    @State private var hasLoadedInitially = false
    
    
    private let primaryColor = Color(red: 0.8, green: 0.2, blue: 0.2) // Vermelho boxe
    
    
    var body: some View {
        NavigationView {
            Group {
               
                if isLoading {
                    ProgressView("Carregando comentários...")
                        .frame(maxWidth: .infinity, maxHeight: .infinity)
                } 
                
                else if userComments.isEmpty {
                    emptyStateView
                } 
               
                else {
                    commentsList
                }
            }
            
            .navigationTitle("Meus Comentários")
            
            
            .refreshable {
                await loadUserComments()
            }
            
            
            .alert("Erro", isPresented: $showError) {
                Button("OK", role: .cancel) {}
            } message: {
                Text(errorMessage)
            }
            
            
            .onAppear {
                // Só carrega se ainda não carregou inicialmente
                if !hasLoadedInitially {
                    hasLoadedInitially = true
                    Task {
                        await loadUserComments()
                    }
                }
            }
        }
    }
    
    
    private var emptyStateView: some View {
        VStack(spacing: 20) {
            
            Image(systemName: "bubble.left.and.bubble.right")
                .font(.system(size: 60))
                .foregroundColor(.gray)
            
            
            Text("Ainda não fez nenhum comentário")
                .font(.title2)
                .fontWeight(.semibold)
            
            
            Text("Explore os blogs da comunidade e partilhe as suas opiniões!")
                .font(.body)
                .foregroundColor(.gray)
                .multilineTextAlignment(.center)
                .padding(.horizontal)
            
            
            NavigationLink(destination: BlogView()) {
                Text("Explorar Blogs")
                    .foregroundColor(.white)
                    .padding()
                    .background(primaryColor)
                    .cornerRadius(10)
            }
        }
        .padding()
    }
    
   
    private var commentsList: some View {
        List {
            
            ForEach(userComments.indices, id: \.self) { index in
                let comment = userComments[index]
                CommentRowView(comment: comment) {
                    deleteComment(at: index)
                }
            }
        }
       
        .listStyle(PlainListStyle())
    }
    
   
    @MainActor
    private func loadUserComments() async {
        guard let user = authManager.currentUser else { return }
        
        // Evita recarregamentos múltiplos
        if isLoading { return }
        
        isLoading = true
        
        do {
            // OPTIMIZAÇÃO: Busca apenas os primeiros 5-10 blogs mais recentes
            let allBlogs = try await APIService.shared.getBlogPosts()
            let recentBlogs = Array(allBlogs.prefix(10)) // Limita a 10 blogs
            
            var allComments: [[String: Any]] = []
            
            // OPTIMIZAÇÃO: Usar TaskGroup para chamadas paralelas em vez de sequenciais
            await withTaskGroup(of: Void.self) { group in
                for blog in recentBlogs {
                    if let blogId = blog["id_post"] as? Int {
                        group.addTask {
                            do {
                                let comments = try await APIService.shared.getBlogComments(postId: blogId)
                                
                                let commentsWithBlogInfo = comments.map { comment in
                                    var commentWithBlog = comment
                                    commentWithBlog["blog_titulo"] = blog["titulo"]
                                    commentWithBlog["blog_id"] = blogId
                                    return commentWithBlog
                                }
                                
                                await MainActor.run {
                                    allComments.append(contentsOf: commentsWithBlogInfo)
                                }
                            } catch {
                                print("Erro ao buscar comentários do blog \(blogId): \(error)")
                            }
                        }
                    }
                }
            }
            
            // Filtra e ordena apenas uma vez no final
            userComments = allComments.filter { comment in
                if let authorId = comment["id_utilizador"] as? Int {
                    return authorId == user.id
                }
                return false
            }.sorted { comment1, comment2 in
                let date1 = comment1["data_comentario"] as? String ?? ""
                let date2 = comment2["data_comentario"] as? String ?? ""
                return date1 > date2
            }
            
        } catch {
            errorMessage = "Erro ao carregar comentários: \(error.localizedDescription)"
            showError = true
        }
        
        isLoading = false
    }
    
    
    private func deleteComment(at index: Int) {
       
        guard let user = authManager.currentUser else { return }
        
       
        let comment = userComments[index]
        
       
        guard let commentId = comment["id_comentario"] as? Int else {
            errorMessage = "Erro: ID do comentário inválido"
            showError = true
            return
        }
        
       
        Task {
            do {
               
                let _ = try await APIService.shared.deleteBlogComment(commentId: commentId, userId: user.id)
                
                
                await MainActor.run {
                    userComments.remove(at: index)
                }
            } catch {
                
                await MainActor.run {
                    errorMessage = "Erro ao eliminar comentário: \(error.localizedDescription)"
                    showError = true
                }
            }
        }
    }
}


struct CommentRowView: View {
    
    let comment: [String: Any]
    
    
    let onDelete: () -> Void
    
    
    var body: some View {
        VStack(alignment: .leading, spacing: 8) {
           
            HStack {
               
                VStack(alignment: .leading, spacing: 4) {
                   
                    Text("Blog: \(comment["blog_titulo"] as? String ?? "Sem título")")
                        .font(.subheadline)
                        .fontWeight(.medium)
                        .foregroundColor(.blue)
                    
                    
                    if let dataComentario = comment["data_comentario"] as? String {
                        Text(formatDate(dataComentario))
                            .font(.caption)
                            .foregroundColor(.gray)
                    }
                }
                
                
                Spacer()
                
               
                Menu {
                    Button("Eliminar", role: .destructive, action: onDelete)
                } label: {
                    Image(systemName: "ellipsis")
                        .foregroundColor(.gray)
                }
            }
            
            
            Text(comment["conteudo"] as? String ?? "Sem conteúdo")
                .font(.body)
                .padding(.top, 4)
            
            
            Divider()
                .padding(.top, 8)
        }
        
        .padding(.vertical, 4)
    }
    
    
    private func formatDate(_ dateString: String) -> String {
        
        let formatter = ISO8601DateFormatter()
        
       
        formatter.formatOptions = [.withInternetDateTime, .withFractionalSeconds]
        
        
        if let date = formatter.date(from: dateString) {
           
            let displayFormatter = DateFormatter()
            displayFormatter.dateStyle = .medium
            displayFormatter.timeStyle = .short
            displayFormatter.locale = Locale(identifier: "pt_PT")
            return displayFormatter.string(from: date)
        }
        
        
        return dateString
    }
}


#Preview {
    UserCommentsView()
        .environmentObject(AuthManager())
}
