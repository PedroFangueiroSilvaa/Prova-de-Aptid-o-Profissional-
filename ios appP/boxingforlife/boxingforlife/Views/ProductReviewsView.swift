import SwiftUI

struct ProductReviewsView: View {
    let codigoBase: String
    @State private var reviews: [ProductReview] = []
    @State private var isLoading = false
    @State private var showError = false
    @State private var errorMessage = ""
    
    var averageRating: Double {
        guard !reviews.isEmpty else { return 0.0 }
        let total = reviews.map { Double($0.classificacao) }.reduce(0, +)
        return total / Double(reviews.count)
    }
    
    // Função pública para recarregar reviews (pode ser chamada de outras Views)
    func reloadReviews() {
        Task {
            await loadReviews()
        }
    }
    
    var body: some View {
        VStack(alignment: .leading, spacing: 16) {
            // Cabeçalho da seção de reviews
            headerSection
            
            if isLoading {
                HStack {
                    Spacer()
                    ProgressView("Carregando avaliações...")
                    Spacer()
                }
            } else if reviews.isEmpty {
                emptyStateView
            } else {
                reviewsListView
            }
        }
        .padding()
        .alert("Erro", isPresented: $showError) {
            Button("OK", role: .cancel) {}
        } message: {
            Text(errorMessage)
        }
        .task {
            await loadReviews()
        }
        .onReceive(NotificationCenter.default.publisher(for: .reviewSubmitted)) { notification in
            // Recarrega reviews quando uma nova review é submetida
            if let submittedCodigoBase = notification.object as? String,
               submittedCodigoBase == codigoBase {
                Task {
                    await loadReviews()
                }
            }
        }
    }
    
    private var headerSection: some View {
        VStack(alignment: .leading, spacing: 8) {
            Text("Avaliações")
                .font(.title2)
                .bold()
            
            if !reviews.isEmpty {
                HStack {
                    // Rating médio com estrelas
                    HStack(spacing: 4) {
                        ForEach(1...5, id: \.self) { star in
                            Image(systemName: star <= Int(averageRating) ? "star.fill" : "star")
                                .foregroundColor(.yellow)
                                .font(.system(size: 16))
                        }
                    }
                    
                    Text(String(format: "%.1f", averageRating))
                        .font(.system(.body, design: .rounded))
                        .bold()
                    
                    Text("(\(reviews.count) avaliações)")
                        .font(.caption)
                        .foregroundColor(.gray)
                    
                    Spacer()
                }
            }
        }
    }
    
    private var emptyStateView: some View {
        VStack(spacing: 12) {
            Image(systemName: "star.slash")
                .font(.system(size: 40))
                .foregroundColor(.gray)
            
            Text("Ainda não há avaliações")
                .font(.headline)
                .foregroundColor(.gray)
            
            Text("Seja o primeiro a avaliar este produto")
                .font(.caption)
                .foregroundColor(.gray)
        }
        .padding(.vertical, 20)
        .frame(maxWidth: .infinity)
    }
    
    private var reviewsListView: some View {
        LazyVStack(spacing: 16) {
            ForEach(reviews) { review in
                ReviewCardView(review: review)
            }
        }
    }
    
    private func loadReviews() async {
        isLoading = true
        
        do {
            reviews = try await APIService.shared.fetchProductReviews(codigoBase: codigoBase)
        } catch {
            errorMessage = "Erro ao carregar avaliações: \(error.localizedDescription)"
            showError = true
        }
        
        isLoading = false
    }
}

struct ReviewCardView: View {
    let review: ProductReview
    
    var body: some View {
        VStack(alignment: .leading, spacing: 8) {
            // Header com nome do utilizador e data
            HStack {
                Text(review.nomeUtilizador ?? "Utilizador Anónimo")
                    .font(.headline)
                    .bold()
                
                Spacer()
                
                Text(formatDate(review.dataReview))
                    .font(.caption)
                    .foregroundColor(.gray)
            }
            
            // Rating com estrelas
            HStack(spacing: 2) {
                ForEach(1...5, id: \.self) { star in
                    Image(systemName: star <= review.classificacao ? "star.fill" : "star")
                        .foregroundColor(.yellow)
                        .font(.system(size: 14))
                }
            }
            
            // Comentário
            if let comentario = review.comentario, !comentario.isEmpty {
                Text(comentario)
                    .font(.body)
                    .padding(.top, 4)
            }
        }
        .padding()
        .background(Color(.systemGray6))
        .cornerRadius(12)
    }
    
    private func formatDate(_ dateString: String) -> String {
        let formatter = DateFormatter()
        // Formato ISO com timezone (como vem da base de dados)
        formatter.dateFormat = "yyyy-MM-dd'T'HH:mm:ss.SSSZ"
        formatter.locale = Locale(identifier: "en_US_POSIX")
        
        // Se falhar, tenta formato sem milissegundos
        if formatter.date(from: dateString) == nil {
            formatter.dateFormat = "yyyy-MM-dd'T'HH:mm:ssZ"
        }
        
        // Se ainda falhar, tenta formato simples
        if formatter.date(from: dateString) == nil {
            formatter.dateFormat = "yyyy-MM-dd HH:mm:ss"
        }
        
        if let date = formatter.date(from: dateString) {
            let displayFormatter = DateFormatter()
            displayFormatter.dateStyle = .medium
            displayFormatter.timeStyle = .none
            displayFormatter.locale = Locale(identifier: "pt_PT")
            return displayFormatter.string(from: date)
        }
        
        // Se não conseguir formatar, retorna apenas a data sem hora
        if dateString.count >= 10 {
            let dateOnly = String(dateString.prefix(10))
            let components = dateOnly.split(separator: "-")
            if components.count == 3 {
                return "\(components[2])/\(components[1])/\(components[0])"
            }
        }
        
        return dateString
    }
}

#Preview {
    ProductReviewsView(codigoBase: "1001-9002-3001")
}

// Extensão para notificações de reviews
extension Notification.Name {
    static let reviewSubmitted = Notification.Name("reviewSubmitted")
}
