
import Foundation // Framework base para tipos de dados, datas, etc.
struct BlogComment: Identifiable {
    
    
    let id: Int
    
   
    let postId: Int
    
   
    let userId: Int
    
    
    let userName: String
    
    
    let content: String
    
   
    let date: Date
    
    
    init(dictionary: [String: Any]) {
        
       
        id = dictionary["id_comentario"] as? Int ?? 0
        
       
        postId = dictionary["id_post"] as? Int ?? 0
        
       
        userId = dictionary["id_utilizador"] as? Int ?? 0
        
        
        userName = dictionary["nome_utilizador"] as? String ?? "Usuário"
        
       
        content = dictionary["conteudo"] as? String ?? ""
        
       
        let dateFormatter = DateFormatter()
        dateFormatter.dateFormat = "yyyy-MM-dd HH:mm:ss" // Formato ISO padrão da MySQL
        
       
        if let dateString = dictionary["data_comentario"] as? String,
           let parsedDate = dateFormatter.date(from: dateString) {
            // Sucesso: ambas as operações funcionaram
            date = parsedDate
        } else {
           
            date = Date()
        }
    }
    
    
    var formattedDate: String {
        
        let formatter = DateFormatter()
        
       
        formatter.dateStyle = .medium
        
       
        formatter.timeStyle = .short
        
       
        return formatter.string(from: date)
    }
}