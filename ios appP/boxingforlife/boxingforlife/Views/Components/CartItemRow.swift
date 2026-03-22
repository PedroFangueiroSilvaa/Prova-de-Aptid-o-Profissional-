import SwiftUI

public struct CartItemRow: View {
    public let item: CartItem
    @State private var imageLoadError = false
    @State private var imageLoadAttempts = 0
    private let maxLoadAttempts = 3
    
    public init(item: CartItem) {
        self.item = item
        print("CartItemRow inicializado com imagem: \(item.imagem)")
    }
    
    public var body: some View {
        HStack {
            if let imageUrl = URL(string: APIService.shared.processImageURL(item.imagem)) {
                AsyncImage(url: imageUrl) { phase in
                    switch phase {
                    case .empty:
                        Color.gray
                            .opacity(0.3)
                    case .success(let image):
                        image
                            .resizable()
                            .aspectRatio(contentMode: .fill)
                    case .failure(let error):
                        Color.gray
                            .opacity(0.3)
                            .onAppear {
                                print("Erro ao carregar imagem: \(error.localizedDescription)")
                                print("URL da imagem: \(imageUrl)")
                                print("Tentativa \(imageLoadAttempts + 1) de \(maxLoadAttempts)")
                                
                                if imageLoadAttempts < maxLoadAttempts {
                                    imageLoadAttempts += 1
                                    // Tentar recarregar a imagem após um breve delay
                                    DispatchQueue.main.asyncAfter(deadline: .now() + 1) {
                                        // Forçar uma nova tentativa de carregamento
                                        imageLoadError = true
                                        imageLoadError = false
                                    }
                                }
                            }
                    @unknown default:
                        Color.gray
                            .opacity(0.3)
                    }
                }
                .frame(width: 60, height: 60)
                .cornerRadius(8)
            } else {
                Color.gray
                    .opacity(0.3)
                    .frame(width: 60, height: 60)
                    .cornerRadius(8)
                    .onAppear {
                        print("URL da imagem inválida: \(item.imagem)")
                    }
            }
            
            VStack(alignment: .leading) {
                Text(item.nome ?? "Produto")
                    .font(.headline)
                Text("\(item.tamanho ?? "") - \(item.cor ?? "")")
                    .font(.subheadline)
                Text("Quantidade: \(item.quantidade)")
                    .font(.subheadline)
                HStack {
                    Text("€\(String(format: "%.2f", item.preco_unitario)) x \(item.quantidade)")
                        .font(.subheadline)
                        .foregroundColor(.gray)
                    Text("€\(String(format: "%.2f", item.preco_unitario * Double(item.quantidade)))")
                        .font(.subheadline)
                        .foregroundColor(.blue)
                }
            }
        }
        .padding(.vertical, 8)
    }
} 