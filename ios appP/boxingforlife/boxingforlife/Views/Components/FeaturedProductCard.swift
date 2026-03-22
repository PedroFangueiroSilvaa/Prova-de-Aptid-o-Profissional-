import SwiftUI

struct FeaturedProductCard: View {
    let name: String
    let price: Double
    let imageUrl: String  // Mudando de imageEmoji para imageUrl
    let description: String
    let onAddToCart: () -> Void
    let productId: Int  // Adicionando ID do produto para navegação
    let codigoBase: String? // Código base para buscar reviews
    
    // Cores atualizadas para o tema laranja
    let cardColor = AppTheme.primaryOrange
    
    var body: some View {
        VStack(alignment: .leading) {
            ZStack(alignment: .topTrailing) {
                // Card background
                RoundedRectangle(cornerRadius: 12)
                    .fill(Color.gray.opacity(0.1))
                
                // Product image - usando AsyncImage para carregar imagens remotas
                AsyncImage(url: URL(string: imageUrl)) { phase in
                    switch phase {
                    case .empty:
                        ProgressView()
                            .frame(maxWidth: .infinity, maxHeight: .infinity)
                    case .success(let image):
                        image
                            .resizable()
                            .aspectRatio(contentMode: .fit)
                            .padding(15)
                            .frame(maxWidth: .infinity, maxHeight: .infinity)
                    case .failure:
                        Image(systemName: "photo")
                            .font(.system(size: 40))
                            .foregroundColor(.gray)
                            .frame(maxWidth: .infinity, maxHeight: .infinity)
                    @unknown default:
                        EmptyView()
                    }
                }
                .frame(height: 140)
                
                // Add to cart button
                Button(action: onAddToCart) {
                    Image(systemName: "plus.circle.fill")
                        .font(.title2)
                        .foregroundColor(cardColor)
                        .padding(8)
                        .background(Circle().fill(Color.white))
                        .shadow(color: Color.black.opacity(0.1), radius: 3, x: 0, y: 1)
                }
                .padding(8)
            }
            .frame(height: 160)
            
            // Product info
            VStack(alignment: .leading, spacing: 4) {
                Text(name)
                    .font(.system(size: 16, weight: .medium))
                    .lineLimit(1)
                
                Text(description)
                    .font(.caption)
                    .foregroundColor(.secondary)
                    .lineLimit(2)
                    .frame(height: 32)
                
                // Rating do produto
                if let codigoBase = codigoBase {
                    ProductRatingView(codigoBase: codigoBase)
                        .padding(.top, 2)
                }
                
                Text("€\(String(format: "%.2f", price))")
                    .font(.system(size: 17, weight: .bold))
                    .foregroundColor(cardColor)
                    .padding(.top, 2)
            }
            .padding(.horizontal, 10)
            .padding(.top, 5)
            .padding(.bottom, 10)
        }
        .frame(width: 170)
        .background(Color.white)
        .cornerRadius(12)
        .shadow(color: Color.black.opacity(0.05), radius: 5, x: 0, y: 2)
    }
}

#Preview {
    HStack(spacing: 16) {
        FeaturedProductCard(
            name: "Luvas Pro Fight",
            price: 79.99,
            imageUrl: "https://example.com/luvas.jpg",
            description: "Luvas profissionais para competição",
            onAddToCart: {},
            productId: 1,
            codigoBase: "1001-9002-3001"
        )
        
        FeaturedProductCard(
            name: "Protetor Bucal Elite",
            price: 19.99,
            imageUrl: "https://example.com/protetor.jpg",
            description: "Protetor bucal de silicone premium",
            onAddToCart: {},
            productId: 2,
            codigoBase: "1002-9001-3002"
        )
    }
    .padding()
    .background(Color.gray.opacity(0.1))
}