import SwiftUI

struct ProductRatingView: View {
    let codigoBase: String
    @State private var averageRating: Double = 0
    @State private var totalReviews: Int = 0
    @State private var isLoading = false
    
    var body: some View {
        HStack(spacing: 4) {
            if isLoading {
                ProgressView()
                    .progressViewStyle(CircularProgressViewStyle())
                    .scaleEffect(0.5)
            } else if totalReviews > 0 {
                // Estrelas
                HStack(spacing: 1) {
                    ForEach(1...5, id: \.self) { star in
                        Image(systemName: star <= Int(averageRating.rounded()) ? "star.fill" : "star")
                            .foregroundColor(.yellow)
                            .font(.system(size: 12))
                    }
                }
                
                // Rating numérico
                Text(String(format: "%.1f", averageRating))
                    .font(.caption)
                    .bold()
                
                // Número de reviews
                Text("(\(totalReviews))")
                    .font(.caption2)
                    .foregroundColor(.gray)
            } else {
                // Sem reviews
                HStack(spacing: 1) {
                    ForEach(1...5, id: \.self) { _ in
                        Image(systemName: "star")
                            .foregroundColor(.gray)
                            .font(.system(size: 12))
                    }
                }
                Text("Sem avaliações")
                    .font(.caption2)
                    .foregroundColor(.gray)
            }
        }
        .task {
            await loadRating()
        }
    }
    
    private func loadRating() async {
        isLoading = true
        
        do {
            let reviews = try await APIService.shared.fetchProductReviews(codigoBase: codigoBase)
            totalReviews = reviews.count
            
            if !reviews.isEmpty {
                let total = reviews.map { Double($0.classificacao) }.reduce(0, +)
                averageRating = total / Double(reviews.count)
            } else {
                averageRating = 0
            }
        } catch {
            // Silencioso - se não conseguir carregar, apenas não mostra nada
            print("Erro ao carregar rating do produto \(codigoBase): \(error)")
        }
        
        isLoading = false
    }
}

#Preview {
    ProductRatingView(codigoBase: "1001-9002-3001")
}
