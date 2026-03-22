

import Foundation
import StripePaymentSheet


class OrderProcessor: ObservableObject {
    @Published var orders: [Order] = []
    @Published var paymentSheet: PaymentSheet?
    @Published var paymentResult: PaymentSheetResult?
    @Published var isPaymentReady = false
    
    // Novas propriedades para a tela de confirmação
    @Published var showOrderCompleted = false
    @Published var completedOrderDetails: [String: Any] = [:]
    @Published var completedOrderItems: [CartItem] = []
    
    private let apiService = APIService.shared
    private let paymentService = PaymentService.shared
    
    
    func getOrders(for userId: Int) async throws {
        var fetchedOrders = try await apiService.getOrders(id_utilizador: userId)
        
        // Ordena encomendas por data decrescente (mais recentes primeiro)
        // Melhora UX mostrando encomendas recentes no topo da lista
        fetchedOrders.sort { (order1, order2) -> Bool in
            guard let date1 = order1.data_encomenda,
                  let date2 = order2.data_encomenda else {
                // Se alguma data for nil, considera como mais antiga
                return false
            }
            return date1 > date2
        }
        
        // Atualiza a propriedade observável (notifica Views automaticamente)
        orders = fetchedOrders
    }
    
    
    @MainActor
    func preparePayment(userId: Int, items: [CartItem], total: Double, address: String) async -> Bool {
        // Reset do estado para nova tentativa de pagamento
        isPaymentReady = false
        
        do {
            // Primeira etapa: Criar Payment Intent no servidor Stripe
            // O PaymentService comunica com o backend que cria o Payment Intent
            let clientSecret = try await paymentService.preparePayment(
                userId: userId,
                cartItems: items,
                total: total,
                shippingAddress: address
            )
            
            print("💳 Client secret obtido: \(clientSecret)")
            
            // Segunda etapa: Configurar o PaymentSheet com dados da empresa
            var configuration = PaymentSheet.Configuration()
            configuration.merchantDisplayName = "Boxing for Life"
            configuration.defaultBillingDetails.address.country = "PT" // Portugal
            configuration.allowsDelayedPaymentMethods = true // Permite SEPA, etc.
            
            // Terceira etapa: Inicializar PaymentSheet com configuração
            paymentSheet = PaymentSheet(
                paymentIntentClientSecret: clientSecret,
                configuration: configuration
            )
            
            // Quarta etapa: Marcar como pronto para mostrar ao utilizador
            isPaymentReady = true
            return true
        } catch {
            print("❌ Erro ao preparar pagamento: \(error)")
            return false
        }
    }
    
    
    func createOrder(userId: Int, items: [CartItem], total: Double, local_envio: String) async throws -> [String: Any] {
        // VALIDAÇÃO CRÍTICA: Só prossegue se pagamento foi bem-sucedido
        if let paymentResult = paymentResult, case .completed = paymentResult {
            print("✅ Pagamento concluído com sucesso, criando encomenda...")
            
            // Estrutura os dados da encomenda para envio ao servidor
            let orderData: [String: Any] = [
                "id_utilizador": userId,
                "itens": items.map { item in
                    [
                        "sku": item.sku,
                        "quantidade": item.quantidade,
                        "preco_unitario": item.preco_unitario
                    ]
                },
                "total": total,
                "local_envio": local_envio
            ]
            
            print("📦 Enviando pedido para o servidor...")
            print("📋 Dados do pedido: \(orderData)")
            
            do {
                // Cria a encomenda no servidor (base de dados)
                let order = try await apiService.createOrder(orderData: orderData)
                print("🎉 Pedido criado com sucesso: \(order)")
                
                // Armazenar detalhes para a tela de confirmação
                await MainActor.run {
                    self.completedOrderDetails = [
                        "id_encomenda": order.id,
                        "total": total,
                        "local_envio": local_envio
                    ]
                    self.completedOrderItems = items
                    self.showOrderCompleted = true
                }
                
                return ["success": true, "orderId": order.id]
            } catch {
                print("💥 Erro ao criar pedido: \(error)")
                throw error
            }
        } else {
            // PROTEÇÃO: Impede criação de encomenda sem pagamento válido
            let errorMessage = "Pagamento não concluído"
            print("❌ \(errorMessage)")
            return ["success": false, "error": errorMessage]
        }
    }
    
    
    @MainActor
    func handlePaymentResult(_ result: PaymentSheetResult) {
        // Armazena resultado para validação posterior
        self.paymentResult = result
        
        // Logging específico baseado no tipo de resultado
        switch result {
        case .completed:
            print("✅ Pagamento concluído com sucesso")
        case .canceled:
            print("🚫 Pagamento cancelado pelo usuário")
        case .failed(let error):
            print("❌ Erro no pagamento: \(error.localizedDescription)")
        }
    }
    
    // Método para limpar estado após mostrar confirmação
    @MainActor
    func resetOrderCompletedState() {
        showOrderCompleted = false
        completedOrderDetails = [:]
        completedOrderItems = []
        paymentResult = nil
        isPaymentReady = false
        paymentSheet = nil
    }
    
    private func handleResponse(_ response: [String: Any]) throws -> [String: Any] {
        guard let success = response["success"] as? Bool else {
            throw NSError(domain: "OrderProcessor", code: -1, userInfo: [NSLocalizedDescriptionKey: "Resposta inválida do servidor"])
        }
        
        if !success {
            let errorMessage = response["error"] as? String ?? "Erro desconhecido"
            throw NSError(domain: "OrderProcessor", code: -1, userInfo: [NSLocalizedDescriptionKey: errorMessage])
        }
        
        return response
    }
}