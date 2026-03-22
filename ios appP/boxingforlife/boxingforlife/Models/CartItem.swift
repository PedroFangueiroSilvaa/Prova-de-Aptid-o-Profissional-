

import Foundation // Framework base para tipos fundamentais e Codable


public struct CartItem: Identifiable, Codable {
    
  
    public let id: Int?
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
    
    
    public init(id: Int? = nil,           // = nil: valor padrão opcional
                sku: String,              // Sem padrão: obrigatório
                quantidade: Int,          // Sem padrão: obrigatório
                preco_unitario: Double,   // Sem padrão: obrigatório
                nome: String,             // Sem padrão: obrigatório
                imagem: String,           // Sem padrão: obrigatório
                tamanho: String,          // Sem padrão: obrigatório
                cor: String) {            // Sem padrão: obrigatório
        
        
        
        self.id = id                           // Pode ser nil inicialmente
        self.sku = sku                         // Identificador único da variação
        self.quantidade = quantidade           // Número de unidades
        self.preco_unitario = preco_unitario   // Preço individual
        self.nome = nome                       // Nome para mostrar na UI
        self.imagem = imagem                   // Path da imagem
        self.tamanho = tamanho                 // Tamanho selecionado
        self.cor = cor                         // Cor selecionada
        
        
    }
} 