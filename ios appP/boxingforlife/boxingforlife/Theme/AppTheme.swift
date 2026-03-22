
import SwiftUI

struct AppTheme {
    // MARK: - Cores Principais (Paleta Laranja)
    // Cores que representam a identidade da marca Boxing for Life
    static let primaryOrange = Color(red: 0.95, green: 0.45, blue: 0.05) // Laranja principal vibrante
    static let secondaryOrange = Color(red: 1.0, green: 0.6, blue: 0.2)  // Laranja secundário mais suave
    static let lightOrange = Color(red: 1.0, green: 0.8, blue: 0.6)      // Laranja claro para backgrounds
    static let darkOrange = Color(red: 0.7, green: 0.3, blue: 0.0)       // Laranja escuro para contraste
    
    // MARK: - Cores de Fundo e Texto
    // Cores neutras para garantir boa legibilidade
    static let background = Color.white                                    // Fundo principal
    static let secondaryBackground = Color(red: 0.98, green: 0.98, blue: 0.98) // Fundo secundário
    static let text = Color.black                                         // Texto principal
    static let secondaryText = Color.gray                                 // Texto secundário
    
    // MARK: - Cores de Estado e Ação
    // Cores para diferentes estados da interface
    static let accent = primaryOrange                                     // Cor de destaque
    static let success = Color.green                                      // Cor para sucessos
    static let warning = Color.yellow                                     // Cor para avisos
    static let error = Color.red                                          // Cor para erros
    
    // MARK: - Cores de Botões
    // Definições específicas para diferentes tipos de botões
    static let buttonBackground = primaryOrange                           // Fundo do botão principal
    static let buttonText = Color.white                                   // Texto do botão principal
    static let secondaryButtonBackground = lightOrange                    // Fundo do botão secundário
    static let secondaryButtonText = darkOrange                          // Texto do botão secundário
    
    // MARK: - Constantes de Design
    // Valores padrão para manter consistência visual
    static let cornerRadius: CGFloat = 8                                  // Raio padrão para cantos arredondados
    static let buttonCornerRadius: CGFloat = 12                          // Raio específico para botões
    static let shadowRadius: CGFloat = 4                                 // Raio padrão para sombras
    
    // MARK: - Métodos para Estilos de Botão
    // Funções que retornam estilos pré-definidos para botões
    static func primaryButtonStyle() -> some ButtonStyle {
        return OrangeButtonStyle()
    }
    
    static func secondaryButtonStyle() -> some ButtonStyle {
        return SecondaryOrangeButtonStyle()
    }
}

// MARK: - Estilos de Botão Personalizados

// Estilo principal para botões laranja (botões de ação primária)
struct OrangeButtonStyle: ButtonStyle {
    // Função que define como o botão vai aparecer e comportar-se
    func makeBody(configuration: Configuration) -> some View {
        configuration.label
            .padding()
            // Muda cor quando pressionado (feedback visual)
            .background(configuration.isPressed ? AppTheme.darkOrange : AppTheme.primaryOrange)
            .foregroundColor(.white)
            .cornerRadius(AppTheme.buttonCornerRadius)
            // Adiciona sombra para profundidade
            .shadow(color: Color.black.opacity(0.2), radius: 3, x: 0, y: 2)
            // Efeito de "pressionar" - botão fica ligeiramente menor quando tocado
            .scaleEffect(configuration.isPressed ? 0.97 : 1)
            // Animação suave para as mudanças de estado
            .animation(.easeInOut(duration: 0.2), value: configuration.isPressed)
    }
}

// Estilo secundário para botões laranja (botões de ação secundária)
struct SecondaryOrangeButtonStyle: ButtonStyle {
    // Função que define como o botão secundário vai aparecer
    func makeBody(configuration: Configuration) -> some View {
        configuration.label
            .padding()
            // Fundo mais claro para botões secundários
            .background(configuration.isPressed ? AppTheme.secondaryOrange : AppTheme.lightOrange)
            .foregroundColor(AppTheme.darkOrange)
            .cornerRadius(AppTheme.buttonCornerRadius)
            // Adiciona borda para definir melhor o botão
            .overlay(
                RoundedRectangle(cornerRadius: AppTheme.buttonCornerRadius)
                    .stroke(AppTheme.secondaryOrange, lineWidth: 1)
            )
            // Mesmo efeito de "pressionar" do botão principal
            .scaleEffect(configuration.isPressed ? 0.97 : 1)
            .animation(.easeInOut(duration: 0.2), value: configuration.isPressed)
    }
}

// MARK: - Extensões para Facilitar Uso

// Extensão que adiciona método para aplicar tema laranja a qualquer View
extension View {
    // Função que aplica o tema laranja em qualquer elemento da interface
    func orangeThemeStyle() -> some View {
        self
            .accentColor(AppTheme.primaryOrange)  // Define cor de destaque
            .tint(AppTheme.primaryOrange)         // Define cor de tint (iOS 15+)
    }
}
