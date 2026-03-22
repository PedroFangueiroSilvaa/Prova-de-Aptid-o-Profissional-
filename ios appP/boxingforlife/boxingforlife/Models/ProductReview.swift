import Foundation

// Modelo para review de produto
struct ProductReview: Identifiable, Codable {
    let id: Int
    let idEncomenda: Int
    let codigoBase: String
    let idUtilizador: Int
    let classificacao: Int
    let comentario: String?
    let dataReview: String
    let nomeUtilizador: String?
    
    enum CodingKeys: String, CodingKey {
        case id = "id_review"
        case idEncomenda = "id_encomenda"
        case codigoBase = "codigo_base"
        case idUtilizador = "id_utilizador"
        case classificacao
        case comentario
        case dataReview = "data_review"
        case nomeUtilizador = "nome_utilizador"
    }
}

// Modelo para review de encomenda
struct OrderReview: Identifiable, Codable {
    let id: Int
    let idEncomenda: Int
    let idUtilizador: Int
    let classificacao: Int
    let comentario: String?
    let dataReview: String
    let nomeUtilizador: String?
    
    enum CodingKeys: String, CodingKey {
        case id = "id_review"
        case idEncomenda = "id_encomenda"
        case idUtilizador = "id_utilizador"
        case classificacao
        case comentario
        case dataReview = "data_review"
        case nomeUtilizador = "nome_utilizador"
    }
}

// Modelo para submissão de review
struct SubmitReview: Codable {
    let classificacao: Int
    let comentario: String
}

// Modelo para resposta da API de reviews de produto
struct ProductReviewsResponse: Codable {
    let success: Bool
    let reviews: [ProductReview]
    let averageRating: Double?
    let totalReviews: Int
    
    enum CodingKeys: String, CodingKey {
        case success
        case reviews
        case averageRating = "average_rating"
        case totalReviews = "total_reviews"
    }
}
