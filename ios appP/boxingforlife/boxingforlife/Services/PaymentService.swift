

import Foundation
import StripePaymentSheet


class PaymentService {
    
    static let shared = PaymentService()
    
    
    private let apiService = APIService.shared
    
    
    private let publishableKey = "pk_test_51QsoQIGP21KyXclQIX92EZlZHpHOKP0yuj7mhOFelZgIOXIdo2j4yoUFexmJywN4jBedj9IqIk9Kwo3B5y4VDdgQ00KwXUsnW4"
    
   
    private init() {
        // Configura o Stripe SDK com a chave pública
        // Esta configuração é global e aplica-se a toda a app
        StripeAPI.defaultPublishableKey = publishableKey
    }
    
    
    func preparePayment(userId: Int, cartItems: [CartItem], total: Double, shippingAddress: String) async throws -> String {
        // Formata os itens do carrinho para estrutura esperada pelo servidor
        let items = cartItems.map { item -> [String: Any] in
            return [
                "sku": item.sku,
                "quantidade": item.quantidade,
                "preco_unitario": item.preco_unitario,
                "nome": item.nome
            ]
        }
        
        // Estrutura completa de dados para criação do Payment Intent
        let paymentData: [String: Any] = [
            "amount": total,           // Valor em euros (servidor converte para cêntimos)
            "currency": "eur",         // Moeda europeia
            "userId": userId,          // Associação ao utilizador
            "items": items,            // Detalhes do que está a ser comprado
            "shippingAddress": shippingAddress  // Endereço de entrega
        ]
        
        // Configuração do request para o endpoint de pagamentos
        let url = URL(string: "\(APIService.baseURL)/payments/create-payment-intent")!
        var request = URLRequest(url: url)
        request.httpMethod = "POST"
        request.setValue("application/json", forHTTPHeaderField: "Content-Type")
        request.httpBody = try JSONSerialization.data(withJSONObject: paymentData)
        
        // Execução do request e obtenção da resposta
        let (data, response) = try await URLSession.shared.data(for: request)
        
        // Validação da resposta HTTP
        guard let httpResponse = response as? HTTPURLResponse else {
            throw APIError.networkError("Resposta inválida do servidor")
        }
        
        if httpResponse.statusCode != 200 {
            // Tenta extrair mensagem de erro específica do servidor
            if let json = try? JSONSerialization.jsonObject(with: data) as? [String: Any],
               let errorMessage = json["error"] as? String {
                throw APIError.serverError(errorMessage)
            } else {
                throw APIError.serverError("Erro ao criar intenção de pagamento: \(httpResponse.statusCode)")
            }
        }
        
        // Parse da resposta bem-sucedida
        guard let json = try JSONSerialization.jsonObject(with: data) as? [String: Any],
              let clientSecret = json["clientSecret"] as? String else {
            throw APIError.decodingError
        }
        
        // Armazena Payment Intent ID para referência futura (opcional)
        // Útil para debugging e tracking de pagamentos
        if let paymentIntentId = json["paymentIntentId"] as? String {
            UserDefaults.standard.set(paymentIntentId, forKey: "lastPaymentIntentId")
        }
        
        return clientSecret
    }
    
    
    func checkPaymentStatus(paymentIntentId: String) async throws -> [String: Any] {
        let url = URL(string: "\(APIService.baseURL)/payments/payment-status/\(paymentIntentId)")!
        
        let (data, response) = try await URLSession.shared.data(from: url)
        
        guard let httpResponse = response as? HTTPURLResponse,
              httpResponse.statusCode == 200 else {
            throw APIError.serverError("Erro ao verificar status do pagamento")
        }
        
        guard let json = try JSONSerialization.jsonObject(with: data) as? [String: Any] else {
            throw APIError.decodingError
        }
        
        return json
    }
}