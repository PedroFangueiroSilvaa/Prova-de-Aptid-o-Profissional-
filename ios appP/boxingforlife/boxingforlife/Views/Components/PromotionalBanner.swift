import SwiftUI

struct PromotionalBanner: View {
    let title: String
    let subtitle: String
    let buttonText: String
    let backgroundColor: Color
    let action: () -> Void
    
    // Podemos usar emojis como placeholders para imagens até termos assets reais
    let imageEmoji: String
    
    var body: some View {
        ZStack(alignment: .leading) {
            // Background with gradient
            Rectangle()
                .fill(
                    LinearGradient(
                        gradient: Gradient(colors: [backgroundColor, backgroundColor.opacity(0.8)]),
                        startPoint: .topLeading,
                        endPoint: .bottomTrailing
                    )
                )
                .cornerRadius(15)
            
            HStack {
                // Text content
                VStack(alignment: .leading, spacing: 8) {
                    Text(title)
                        .font(.system(size: 24, weight: .bold))
                        .foregroundColor(.white)
                    
                    Text(subtitle)
                        .font(.system(size: 16))
                        .foregroundColor(.white.opacity(0.9))
                        .lineLimit(2)
                    
                    Button(action: action) {
                        Text(buttonText)
                            .font(.system(size: 14, weight: .bold))
                            .foregroundColor(backgroundColor)
                            .padding(.horizontal, 16)
                            .padding(.vertical, 8)
                            .background(Color.white)
                            .cornerRadius(AppTheme.buttonCornerRadius)
                            .shadow(color: Color.black.opacity(0.1), radius: 2, x: 0, y: 1)
                    }
                    .padding(.top, 10)
                }
                .frame(maxWidth: .infinity * 0.6, alignment: .leading)
                .padding(.leading, 20)
                
                Spacer()
                
                // Emoji ou imagem placeholder
                Text(imageEmoji)
                    .font(.system(size: 70))
                    .padding(.trailing, 20)
            }
            .padding(.vertical, 20)
        }
    }
}

#Preview {
    VStack {
        PromotionalBanner(
            title: "NOVA COLEÇÃO",
            subtitle: "Luvas profissionais com 20% de desconto",
            buttonText: "Ver Mais",
            backgroundColor: AppTheme.primaryOrange,
            action: {},
            imageEmoji: "🥊"
        )
        .frame(height: 180)
        .padding()
        
        PromotionalBanner(
            title: "TREINO INTENSO",
            subtitle: "Equipamentos para treinamento avançado",
            buttonText: "Comprar",
            backgroundColor: AppTheme.darkOrange,
            action: {},
            imageEmoji: "🥋"
        )
        .frame(height: 180)
        .padding()
    }
}