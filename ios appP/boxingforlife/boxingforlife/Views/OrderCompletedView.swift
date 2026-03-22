import SwiftUI

struct OrderCompletedView: View {
    let orderDetails: [String: Any]
    let purchasedItems: [CartItem]
    
    @Environment(\.dismiss) private var dismiss
    @EnvironmentObject var cartManager: CartManager
    @EnvironmentObject var orderProcessor: OrderProcessor
    
    var body: some View {
        ScrollView {
            VStack(spacing: 20) {
                // Header de sucesso
                VStack(spacing: 10) {
                    Image(systemName: "checkmark.circle.fill")
                        .font(.system(size: 80))
                        .foregroundColor(.green)
                    
                    Text("Compra Concluída!")
                        .font(.largeTitle)
                        .fontWeight(.bold)
                        .foregroundColor(.primary)
                    
                    Text("Obrigado pela sua compra")
                        .font(.title2)
                        .foregroundColor(.secondary)
                }
                .padding(.top, 20)
                    
                    // Detalhes da encomenda
                    VStack(alignment: .leading, spacing: 15) {
                        Text("Detalhes da Encomenda")
                            .font(.headline)
                            .fontWeight(.semibold)
                        
                        if let orderId = orderDetails["id_encomenda"] as? Int {
                            DetailRow(title: "Número da Encomenda", value: "#\(orderId)")
                        }
                        
                        if let total = orderDetails["total"] as? Double {
                            DetailRow(title: "Total Pago", value: "€\(String(format: "%.2f", total))")
                        }
                        
                        if let address = orderDetails["local_envio"] as? String {
                            DetailRow(title: "Endereço de Entrega", value: address)
                        }
                        
                        DetailRow(title: "Status", value: "Pago")
                        DetailRow(title: "Data", value: formatCurrentDate())
                    }
                    .padding()
                    .background(Color(.systemGray6))
                    .cornerRadius(12)
                    
                    // Lista de produtos comprados
                    VStack(alignment: .leading, spacing: 15) {
                        Text("Produtos Comprados")
                            .font(.headline)
                            .fontWeight(.semibold)
                        
                        ForEach(purchasedItems) { item in
                            PurchasedItemRow(item: item)
                        }
                    }
                    .padding()
                    .background(Color(.systemGray6))
                    .cornerRadius(12)
                    
                    // Informações adicionais
                    VStack(alignment: .leading, spacing: 10) {
                        Text("Próximos Passos")
                            .font(.headline)
                            .fontWeight(.semibold)
                        
                        VStack(alignment: .leading, spacing: 8) {
                            HStack {
                                Image(systemName: "envelope.fill")
                                    .foregroundColor(.blue)
                                Text("Receberá um email de confirmação em breve")
                                    .font(.body)
                            }
                            
                            HStack {
                                Image(systemName: "truck.box.fill")
                                    .foregroundColor(.orange)
                                Text("A sua encomenda será processada em 1-2 dias úteis")
                                    .font(.body)
                            }
                            
                            HStack {
                                Image(systemName: "bell.fill")
                                    .foregroundColor(.green)
                                Text("Será notificado quando a encomenda for enviada")
                                    .font(.body)
                            }
                        }
                    }
                    .padding()
                    .background(Color(.systemGray6))
                    .cornerRadius(12)
                    
                    Spacer(minLength: 30)
                    
                    // Botões de ação
                    VStack(spacing: 15) {
                        NavigationLink(destination: OrdersView()) {
                            Text("Ver as minhas encomendas")
                                .font(.headline)
                                .foregroundColor(.white)
                                .frame(maxWidth: .infinity)
                                .frame(height: 50)
                                .background(Color.blue)
                                .cornerRadius(10)
                        }
                        .simultaneousGesture(TapGesture().onEnded {
                            orderProcessor.resetOrderCompletedState()
                        })
                        
                        Button(action: {
                            orderProcessor.resetOrderCompletedState()
                            dismiss()
                        }) {
                            Text("Continuar a comprar mais produtos")
                                .font(.headline)
                                .foregroundColor(.blue)
                                .frame(maxWidth: .infinity)
                                .frame(height: 50)
                                .background(Color.clear)
                                .overlay(
                                    RoundedRectangle(cornerRadius: 10)
                                        .stroke(Color.blue, lineWidth: 2)
                                )
                        }
                    }
                }
                .padding()
            }
            .navigationTitle("Compra Concluída")
            .navigationBarTitleDisplayMode(.inline)
            .toolbar {
                ToolbarItem(placement: .navigationBarTrailing) {
                    Button("Fechar") {
                        orderProcessor.resetOrderCompletedState()
                        dismiss()
                    }
                }
            }
        .onAppear {
            // Limpar carrinho após compra bem-sucedida
            Task {
                await cartManager.clearCart()
            }
        }
    }
    
    private func formatCurrentDate() -> String {
        let formatter = DateFormatter()
        formatter.dateStyle = .medium
        formatter.timeStyle = .short
        formatter.locale = Locale(identifier: "pt_PT")
        return formatter.string(from: Date())
    }
}

struct DetailRow: View {
    let title: String
    let value: String
    
    var body: some View {
        HStack {
            Text(title)
                .font(.body)
                .foregroundColor(.secondary)
            Spacer()
            Text(value)
                .font(.body)
                .fontWeight(.medium)
                .foregroundColor(.primary)
        }
    }
}

struct PurchasedItemRow: View {
    let item: CartItem
    
    private var imageURL: String {
        // Se a imagem já é uma URL completa (começa com http), usa diretamente
        if item.imagem.hasPrefix("http") {
            return item.imagem
        }
        // Caso contrário, processa através do APIService
        return APIService.shared.processImageURL(item.imagem)
    }
    
    var body: some View {
        HStack(spacing: 12) {
            // Imagem do produto
            AsyncImage(url: URL(string: imageURL)) { phase in
                switch phase {
                case .success(let image):
                    image
                        .resizable()
                        .aspectRatio(contentMode: .fill)
                        .onAppear {
                            print("✅ Imagem carregada com sucesso: \(item.nome)")
                        }
                case .failure(let error):
                    // Imagem padrão quando falha o carregamento
                    VStack(spacing: 4) {
                        Image(systemName: "photo.circle")
                            .font(.system(size: 24))
                            .foregroundColor(.gray)
                        Text("Sem foto")
                            .font(.caption2)
                            .foregroundColor(.gray)
                    }
                    .frame(maxWidth: .infinity, maxHeight: .infinity)
                    .background(Color.gray.opacity(0.1))
                    .onAppear {
                        print("❌ Erro ao carregar imagem do produto: \(item.nome)")
                        print("❌ Imagem original: \(item.imagem)")
                        print("❌ URL final: \(imageURL)")
                        print("❌ Erro: \(error)")
                    }
                case .empty:
                    // Placeholder enquanto carrega
                    ProgressView()
                        .frame(width: 60, height: 60)
                        .onAppear {
                            print("🔄 Carregando imagem do produto: \(item.nome)")
                            print("🔄 Imagem original: \(item.imagem)")
                            print("🔄 URL final: \(imageURL)")
                        }
                @unknown default:
                    // Fallback para estados futuros
                    Rectangle()
                        .fill(Color.gray.opacity(0.3))
                }
            }
            .frame(width: 60, height: 60)
            .cornerRadius(8)
            .clipped()
            
            // Detalhes do produto
            VStack(alignment: .leading, spacing: 4) {
                Text(item.nome)
                    .font(.body)
                    .fontWeight(.medium)
                    .lineLimit(2)
                
                HStack {
                    if !item.tamanho.isEmpty {
                        Text("Tamanho: \(item.tamanho)")
                            .font(.caption)
                            .foregroundColor(.secondary)
                    }
                    
                    if !item.cor.isEmpty {
                        Text("Cor: \(item.cor)")
                            .font(.caption)
                            .foregroundColor(.secondary)
                    }
                }
                
                HStack {
                    Text("Qtd: \(item.quantidade)")
                        .font(.caption)
                        .foregroundColor(.secondary)
                    
                    Spacer()
                    
                    Text("€\(String(format: "%.2f", item.preco_total))")
                        .font(.body)
                        .fontWeight(.semibold)
                        .foregroundColor(.primary)
                }
            }
            
            Spacer()
        }
        .padding(.vertical, 8)
    }
}

#Preview {
    let sampleItems = [
        CartItem(sku: "LUVA001-M-AZUL", quantidade: 2, preco_unitario: 25.99, nome: "Luvas de Boxe Premium", imagem: "produtos/luvas.jpg", tamanho: "M", cor: "Azul"),
        CartItem(sku: "SACO001-L-PRETO", quantidade: 1, preco_unitario: 89.99, nome: "Saco de Boxe Profissional", imagem: "produtos/saco.jpg", tamanho: "L", cor: "Preto")
    ]
    
    let sampleOrderDetails: [String: Any] = [
        "id_encomenda": 12345,
        "total": 141.97,
        "local_envio": "Rua da Amoreira, 123, 1200-001 Lisboa"
    ]
    
    OrderCompletedView(orderDetails: sampleOrderDetails, purchasedItems: sampleItems)
        .environmentObject(CartManager())
        .environmentObject(OrderProcessor())
}
