
import Foundation // Para UserDefaults, Set, etc.


class FavoritesManager: ObservableObject {
    
   
    static let shared = FavoritesManager()
    
   
    private let favoritesKey = "favoriteProducts"
    
   
    @Published private(set) var favorites: Set<String> = []
    
    
    @Published private(set) var isLoading = false
    
   
    @Published private(set) var error: String?
    
    
    private init() {
        
        loadLocalFavorites()
        
       
        Task {
            await syncWithServerIfLoggedIn()
        }
    }
    
    
    func isFavorite(codigoBase: String) -> Bool {
        // Set.contains(): operação O(1) - muito eficiente
        // Verifica se o código do produto existe no conjunto de favoritos
        return favorites.contains(codigoBase)
    }
    
   
    func addFavorite(codigoBase: String) {
        
        print("🟡 [FavoritesManager] Adicionando favorito: \(codigoBase)")
        print("🟡 [FavoritesManager] Utilizador logado: \(AuthManager.shared.isLoggedIn)")
        print("🟡 [FavoritesManager] ID do utilizador: \(AuthManager.shared.userId ?? -1)")
        
        
        favorites.insert(codigoBase)
        
        saveLocalFavorites()
        
       
        Task {
            // await: aguarda operação assíncrona terminar
            await addFavoriteToServerIfLoggedIn(codigoBase: codigoBase)
        }
    }
    
    
    
    func removeFavorite(codigoBase: String) {
        
        
        print("🔴 [FavoritesManager] Removendo favorito: \(codigoBase)")
        print("🔴 [FavoritesManager] Utilizador logado: \(AuthManager.shared.isLoggedIn)")
        print("🔴 [FavoritesManager] ID do utilizador: \(AuthManager.shared.userId ?? -1)")
        
        
        
        favorites.remove(codigoBase)
        

        saveLocalFavorites()
        
        
        
        Task {
            await removeFavoriteFromServerIfLoggedIn(codigoBase: codigoBase)
        }
    }
    
   
   
   
   
    @MainActor
    private func addFavoriteToServerIfLoggedIn(codigoBase: String) async {
        
       
       
        guard let userId = AuthManager.shared.userId else {
            print("🟡 [FavoritesManager] Utilizador não está logado, favorito salvo apenas localmente")
            return // Termina função aqui, não tenta sincronizar
        }
        
        print("🟡 [FavoritesManager] Adicionando favorito ao servidor para utilizador \(userId)")
        
       
       
        do {
            
            let (success, message) = try await APIService.shared.addFavorite(idUtilizador: userId, codigoBase: codigoBase)
            
            if !success {
                print("🔴 [FavoritesManager] Falha ao adicionar favorito no servidor: \(message)")
                
                // ROLLBACK: reverte mudança local que foi feita optimistically
                // Remove o favorito que foi adicionado localmente
                favorites.remove(codigoBase)
                saveLocalFavorites() // Persiste rollback
                
                // Atualiza estado de erro para mostrar na UI
                error = "Erro ao adicionar favorito no servidor: \(message)"
            } else {
                // ========================================
                // SUCESSO - OPERAÇÃO COMPLETOU
                // ========================================
                print("✅ [FavoritesManager] Favorito adicionado com sucesso no servidor")
                error = nil // Limpa qualquer erro anterior
            }
            
        } catch {
           
            print("🔴 [FavoritesManager] Erro ao adicionar favorito no servidor: \(error)")
            
            // ROLLBACK: reverte mudança local
            favorites.remove(codigoBase)
            saveLocalFavorites()
            
            // error.localizedDescription: mensagem de erro localizada do sistema
            self.error = "Erro ao adicionar favorito: \(error.localizedDescription)"
        }
    }
    
    
    @MainActor
    private func removeFavoriteFromServerIfLoggedIn(codigoBase: String) async {
        
        
        guard let userId = AuthManager.shared.userId else {
            print("🔴 [FavoritesManager] Utilizador não está logado, favorito removido apenas localmente")
            return
        }
        
        print("🔴 [FavoritesManager] Removendo favorito do servidor para utilizador \(userId)")
        
        
        do {
            let (success, message) = try await APIService.shared.removeFavorite(idUtilizador: userId, codigoBase: codigoBase)
            
            if !success {
                print("🔴 [FavoritesManager] Falha ao remover favorito no servidor: \(message)")
                
                // ROLLBACK: adiciona de volta o favorito que foi removido localmente
                favorites.insert(codigoBase)
                saveLocalFavorites()
                error = "Erro ao remover favorito no servidor: \(message)"
            } else {
                print("✅ [FavoritesManager] Favorito removido com sucesso no servidor")
                error = nil
            }
            
        } catch {
            print("🔴 [FavoritesManager] Erro ao remover favorito no servidor: \(error)")
            
            // ROLLBACK: adiciona de volta
            favorites.insert(codigoBase)
            saveLocalFavorites()
            self.error = "Erro ao remover favorito: \(error.localizedDescription)"
        }
    }
    
   
    @MainActor
    func syncWithServer() async {
        
        
        guard let userId = AuthManager.shared.userId else {
            print("Utilizador não está logado, não é possível sincronizar favoritos")
            return
        }
        
        
        isLoading = true
        error = nil // Limpa erros anteriores
        
        do {
            
            let serverFavorites = try await APIService.shared.getFavorites(idUtilizador: userId)
            
            
            let serverFavoritesSet = Set(serverFavorites.compactMap { $0["codigo_base"] as? String })
            
           
            favorites = serverFavoritesSet
            
            saveLocalFavorites()
            
            print("Favoritos sincronizados com o servidor: \(serverFavorites.count) favoritos")
            
        } catch {
           
            self.error = "Erro ao sincronizar favoritos: \(error.localizedDescription)"
            print("Erro ao sincronizar favoritos com o servidor: \(error)")
        }
        
       
        isLoading = false
    }
    
    
    @MainActor
    private func syncWithServerIfLoggedIn() async {
        
        if AuthManager.shared.isLoggedIn {
            await syncWithServer() // Chama método de sincronização completa
        }
        // Se não logado, não faz nada (favoritos ficam só locais)
    }
    
    
    func checkFavoriteStatus(codigoBase: String) async -> Bool {
        
       
        guard let userId = AuthManager.shared.userId else {
            // Se não logado, usa apenas cache local
            return isFavorite(codigoBase: codigoBase)
        }
        
        do {
            
            return try await APIService.shared.checkIsFavorite(idUtilizador: userId, codigoBase: codigoBase)
            
        } catch {
           
            print("Erro ao verificar status do favorito: \(error)")
            // Se rede falhar, usa o que temos localmente
            return isFavorite(codigoBase: codigoBase)
        }
    }
    
    private func saveLocalFavorites() {
        
       
        let array = Array(favorites)
        
       
        UserDefaults.standard.set(array, forKey: favoritesKey)
        
        
    }
    
   
    private func loadLocalFavorites() {
        
       
        if let array = UserDefaults.standard.array(forKey: favoritesKey) as? [String] {
            
           
            favorites = Set(array)
            
           
            
        } else {
           
        }
        
        
    }
} 