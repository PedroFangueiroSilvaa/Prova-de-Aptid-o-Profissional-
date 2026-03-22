
import Foundation // Framework base para Codable e tipos fundamentais


public struct OrderItemDetail: Identifiable, Codable {
    
    
    public var id: Int?
    
   
    public let sku: String
    
    
    public let quantidade: Int
    
    
    public let preco_unitario: Double
    
   
    public let nome: String
    
   
    public let imagem: String
    
    
    public let tamanho: String
    
    
    public let cor: String
    
    
    public var preco_total: Double {
       
        return preco_unitario * Double(quantidade)
    }
    
    
    public init(id: Int? = nil,                    // = nil: valor padrão
                sku: String,                       // Obrigatório: identificador único
                quantidade: Int,                   // Obrigatório: número de unidades
                preco_unitario: Double,            // Obrigatório: preço por unidade
                nome: String,                      // Obrigatório: nome do produto
                imagem: String,                    // Obrigatório: caminho da imagem
                tamanho: String,                   // Obrigatório: tamanho selecionado
                cor: String) {                     // Obrigatório: cor selecionada
        
        
        
        self.id = id                               // Pode ser nil inicialmente
        self.sku = sku                            // Código único da variação
        self.quantidade = quantidade              // Número de unidades
        self.preco_unitario = preco_unitario      // Preço histórico
        self.nome = nome                          // Nome completo do produto
        self.imagem = imagem                      // Path da imagem
        self.tamanho = tamanho                    // Tamanho específico
        self.cor = cor                            // Cor específica
        
       
    }
} 