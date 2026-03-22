
import Foundation // Framework base para Date, Codable, etc.


public struct OrderItem: Identifiable, Codable {
    
    
    public let id: Int?
    
    
    public let sku: String
    
   
    public let quantidade: Int
    
    public let preco_unitario: Double
    
    
    public let nome: String?
    
    
    public let imagem: String?
    
    
    public let tamanho: String?
    
    
    public let cor: String?
    
   
    public var preco_total: Double {
       
        return Double(quantidade) * preco_unitario
    }
    
   
    public enum CodingKeys: String, CodingKey {
        
        case id = "id_item"
        
        
        case sku           // sku ↔ sku
        case quantidade    // quantidade ↔ quantidade  
        case preco_unitario // preco_unitario ↔ preco_unitario
        case nome          // nome ↔ nome
        case imagem        // imagem ↔ imagem
        case tamanho       // tamanho ↔ tamanho
        case cor           // cor ↔ cor
        
        
    }
}


public struct Order: Identifiable, Codable {
    
    
    public let id: Int
    
    
    public let id_utilizador: Int?
    
   
    public let items: [OrderItem]?
    
    
    public let total: Double
    
   
    public let local_envio: String?
    
   
    public let data_encomenda: String?
    
   
    public let status: String
    
    
    enum CodingKeys: String, CodingKey {
       
        case id = "id_encomenda"
        
       
        case id_utilizador     // id_utilizador ↔ id_utilizador
        case items            // items ↔ items (array de itens)
        case total            // total ↔ total
        case local_envio      // local_envio ↔ local_envio
        case data_encomenda   // data_encomenda ↔ data_encomenda
        case status           // status ↔ status
    }
    
    
    public var orderStatus: OrderStatus {
        
        return OrderStatus(rawValue: status) ?? .pending
    }
}


public enum OrderStatus: String, Codable {
    
   
    case pending = "pendente"
    
   
    case paid = "pago"
    
    
    case shipped = "enviado"
    
    
    case cancelled = "cancelado"
    
    
    public var displayName: String {
        
        switch self {
        case .pending:
            // Estado de espera: encomenda criada mas não paga
            return "Pendente"
            
        case .paid:
            // Estado ativo: paga e pronta para processamento
            return "Pago"
            
        case .shipped:
            // Estado de transição: a caminho do cliente
            return "Enviado"
            
        case .cancelled:
            // Estado final: operação cancelada
            return "Cancelado"
        }
        
        
    }
}