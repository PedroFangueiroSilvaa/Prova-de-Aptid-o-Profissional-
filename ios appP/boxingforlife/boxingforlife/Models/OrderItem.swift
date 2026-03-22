public struct OrderLineItem: Identifiable, Codable {
    public var id: Int?
    public let sku: String
    public let nome: String
    public let preco_unitario: Double
    public let quantidade: Int
    public let imagem: String
    public let tamanho: String
    public let cor: String
    
    public var preco_total: Double {
        return preco_unitario * Double(quantidade)
    }
    
    public init(id: Int? = nil,
               sku: String,
               quantidade: Int,
               preco_unitario: Double,
               nome: String,
               imagem: String,
               tamanho: String,
               cor: String) {
        self.id = id
        self.sku = sku
        self.quantidade = quantidade
        self.preco_unitario = preco_unitario
        self.nome = nome
        self.imagem = imagem
        self.tamanho = tamanho
        self.cor = cor
    }
} 