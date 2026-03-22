import Foundation  // Para UserDefaults, JSONEncoder/Decoder
import SwiftUI     // Para @Published e reatividade
import Combine     // Para ObservableObject protocol


class AuthManager: ObservableObject {
    
    
    static let shared = AuthManager()
    
    
    @Published var isLoggedIn = false
    
    
    @Published var currentUser: User? {
        didSet {
            
            print("[AuthManager] currentUser atualizado: \(String(describing: currentUser))")
        }
    }
    
  
    var userId: Int? {
        let userIdFromCurrent = currentUser?.id
        let userIdFromDefaults = UserDefaults.standard.object(forKey: "userId") as? Int
        print("🔍 [AuthManager] userId - CurrentUser: \(userIdFromCurrent ?? -1), UserDefaults: \(userIdFromDefaults ?? -1)")
        print("🔍 [AuthManager] isLoggedIn: \(isLoggedIn)")
        return userIdFromCurrent ?? userIdFromDefaults
    }
    private var userAddresses: [Int: String] = [:]
    init() {
        self.isLoggedIn = UserDefaults.standard.bool(forKey: "isLoggedIn")
        if let userData = UserDefaults.standard.data(forKey: "userData"),
           let user = try? JSONDecoder().decode(User.self, from: userData) {
            self.currentUser = user
            
            // Carrega endereço específico do utilizador se disponível
            if let userId = user.id as Int? {
                let key = shippingAddressKey(for: userId)
                if let address = UserDefaults.standard.string(forKey: key) {
                    userAddresses[userId] = address
                }
            }
        }
        
        // Migra endereços antigos para o novo formato (retrocompatibilidade)
        migrateOldAddresses()
    }
    
    // Função que migra endereços do formato antigo (genérico) para o novo (específico por utilizador)
    // Garante que utilizadores antigos não percam os seus endereços guardados
    private func migrateOldAddresses() {
        // Verifica se existe endereço no formato antigo
        if let oldAddress = UserDefaults.standard.string(forKey: "userShippingAddress") {
            print("Encontrado endereço antigo no formato genérico: \(oldAddress)")
            
            // Se há utilizador logado, associa o endereço antigo a ele
            if let userId = currentUser?.id {
                let key = shippingAddressKey(for: userId)
                UserDefaults.standard.set(oldAddress, forKey: key)
                userAddresses[userId] = oldAddress
                print("Endereço migrado para o formato específico do usuário \(userId)")
            }
            
            // Remove o endereço do formato antigo para evitar confusões futuras
            UserDefaults.standard.removeObject(forKey: "userShippingAddress")
            print("Chave antiga 'userShippingAddress' removida")
        }
    }
    
    // Função auxiliar que cria uma chave única para o endereço de envio de cada utilizador
    // Permite que diferentes utilizadores tenham endereços diferentes guardados
    private func shippingAddressKey(for userId: Int) -> String {
        return "userShippingAddress_\(userId)"
    }
    
    // Função principal que executa o login de um utilizador
    // Define o utilizador como logado e guarda as suas informações
    func login(userId: Int, userName: String) {
        print("[AuthManager] Iniciando login do usuário ID: \(userId), Nome: \(userName)")
        
        // Busca o email do utilizador guardado previamente nos UserDefaults
        let userEmail = UserDefaults.standard.string(forKey: "userEmail") ?? ""
        
        // Busca o endereço de envio específico para este utilizador
        let key = shippingAddressKey(for: userId)
        let shippingAddress = UserDefaults.standard.string(forKey: key)
        
        if let address = shippingAddress {
            print("[AuthManager] Endereço de envio encontrado para o usuário \(userId): \(address)")
            userAddresses[userId] = address
        } else {
            print("[AuthManager] Nenhum endereço de envio encontrado para o usuário \(userId)")
        }
        
        // Cria objeto User com todas as informações disponíveis
        let user = User(id: userId, nome: userName, email: userEmail, shippingAddress: shippingAddress)
        
        // Guarda todas as informações necessárias nos UserDefaults para persistir entre sessões
        UserDefaults.standard.set(true, forKey: "isLoggedIn")
        UserDefaults.standard.set(userId, forKey: "userId")
        UserDefaults.standard.set(userId, forKey: "current_user_id") // Para compatibilidade com reviews
        UserDefaults.standard.set(userName, forKey: "userName")
        UserDefaults.standard.set(userName, forKey: "current_user_name") // Para compatibilidade com reviews
        
        if let userData = try? JSONEncoder().encode(user) {
            UserDefaults.standard.set(userData, forKey: "userData")
        }
        
        DispatchQueue.main.async {
            self.isLoggedIn = true
            self.currentUser = user
            print("[AuthManager] Usuário atualizado: \(String(describing: self.currentUser))")
            print("[AuthManager] Endereço de envio: \(String(describing: user.shippingAddress))")
        }
    }
    
    func signIn(email: String, password: String) async throws {
        do {
            // Primeiro faz o login para obter o ID do usuário
            let response = try await APIService.shared.login(email: email, palavra_passe: password)
            
            if let userId = response["id"] as? Int {
                await CartManager.shared.transferCartOnLogin(userId: userId)
                
                UserDefaults.standard.set(userId, forKey: "userId")
                UserDefaults.standard.set(true, forKey: "isLoggedIn")
                UserDefaults.standard.set(email, forKey: "userEmail")
                
                // Agora busca o perfil completo do usuário para obter o endereço de envio
                do {
                    let userProfile = try await APIService.shared.getUserProfile(id: userId)
                    
                    // Extrair o endereço de envio do perfil
                    let shippingAddress = userProfile["local_envio"] as? String
                    let nome = userProfile["nome"] as? String ?? ""
                    let emailFromAPI = userProfile["email"] as? String ?? email
                    print("[AuthManager] Endereço de envio obtido da API: \(String(describing: shippingAddress))")
                    
                    // Armazenar o endereço usando a chave específica para este usuário
                    if let address = shippingAddress, !address.isEmpty {
                        let key = shippingAddressKey(for: userId)
                        UserDefaults.standard.set(address, forKey: key)
                        userAddresses[userId] = address
                        print("[AuthManager] Endereço de envio armazenado para userId \(userId): \(address)")
                    } else {
                        print("[AuthManager] Nenhum endereço de envio encontrado no perfil do usuário")
                        if let savedAddress = userAddresses[userId] {
                            print("[AuthManager] Usando endereço salvo anteriormente: \(savedAddress)")
                        } else if let savedAddress = UserDefaults.standard.string(forKey: shippingAddressKey(for: userId)) {
                            userAddresses[userId] = savedAddress
                            print("[AuthManager] Recuperado endereço salvo: \(savedAddress)")
                        }
                    }
                    // Atualizar o usuário atual com os dados obtidos
                    await MainActor.run {
                        self.isLoggedIn = true
                        self.currentUser = User(
                            id: userId,
                            nome: nome,
                            email: emailFromAPI,
                            profileImage: nil,
                            shippingAddress: userAddresses[userId] ?? shippingAddress
                        )
                        print("[AuthManager] Usuário atualizado: \(String(describing: self.currentUser))")
                    }
                } catch {
                    print("[AuthManager] Erro ao buscar perfil do usuário: \(error)")
                    let savedAddress = userAddresses[userId] ?? UserDefaults.standard.string(forKey: shippingAddressKey(for: userId))
                    await MainActor.run {
                        self.isLoggedIn = true
                        self.currentUser = User(
                            id: userId,
                            nome: response["nome"] as? String ?? "",
                            email: email,
                            profileImage: nil,
                            shippingAddress: savedAddress
                        )
                    }
                }
            } else {
                throw APIError.serverError("Invalid user data")
            }
        } catch {
            throw error
        }
    }

    func signOut() {
        print("[AuthManager] Iniciando logout")
        
        // Salvar o ID do usuário atual antes de limpar para referência
        let currentUserId = currentUser?.id
        
        // Limpar dados da sessão
        UserDefaults.standard.removeObject(forKey: "isLoggedIn")
        UserDefaults.standard.removeObject(forKey: "userName")
        UserDefaults.standard.removeObject(forKey: "userData")
        UserDefaults.standard.removeObject(forKey: "userShippingAddress") // Remover chave antiga se existir
        UserDefaults.standard.removeObject(forKey: "current_user_id") // Limpar ID para reviews
        UserDefaults.standard.removeObject(forKey: "current_user_name") // Limpar nome para reviews
        
        // Não remover endereços específicos de usuários para preservá-los para futuros logins
        
        // Salvar mudanças
        UserDefaults.standard.set(false, forKey: "isLoggedIn")
        UserDefaults.standard.removeObject(forKey: "userId")
        
        // Notificar o CartManager
        CartManager.shared.handleLogout()
        
        // Atualizar estado publicado
        DispatchQueue.main.async {
            self.isLoggedIn = false
            self.currentUser = nil as User?
            print("[AuthManager] Logout concluído - usuário definido como nil")
        }
        
        if let userId = currentUserId {
            print("[AuthManager] Logout do usuário com ID \(userId) concluído")
        }
    }
    
    func updateProfile(name: String, email: String) async throws {
        guard var user = currentUser else { return }
        
        // Preserva o endereço de envio ao atualizar o perfil
        user = User(id: user.id, nome: name, email: email, profileImage: user.profileImage, shippingAddress: user.shippingAddress)
        
        if let userData = try? JSONEncoder().encode(user) {
            UserDefaults.standard.set(userData, forKey: "userData")
        }
        
        await MainActor.run {
            self.currentUser = user
        }
    }
    
    func changePassword(currentPassword: String, newPassword: String) async throws {
        // Implement password change logic here
        // This should make an API call to update the password
    }
    
    func getUserShippingAddress() -> String? {
        guard let userId = currentUser?.id else {
            print("[AuthManager] Não há usuário logado para obter endereço")
            return nil
        }
        
        // 1. Primeiro tenta obter do cache em memória
        if let cachedAddress = userAddresses[userId], !cachedAddress.isEmpty {
            print("[AuthManager] Endereço obtido do cache em memória: \(cachedAddress)")
            return cachedAddress
        }
        
        // 2. Depois tenta do currentUser.shippingAddress
        if let userAddress = currentUser?.shippingAddress, !userAddress.isEmpty {
            print("[AuthManager] Endereço obtido do currentUser: \(userAddress)")
            userAddresses[userId] = userAddress // Atualiza o cache
            return userAddress
        }
        
        // 3. Por fim, tenta dos UserDefaults usando a chave específica
        let key = shippingAddressKey(for: userId)
        if let savedAddress = UserDefaults.standard.string(forKey: key), !savedAddress.isEmpty {
            print("[AuthManager] Endereço obtido dos UserDefaults para userId \(userId): \(savedAddress)")
            userAddresses[userId] = savedAddress // Atualiza o cache
            return savedAddress
        }
        
        print("[AuthManager] Nenhum endereço de envio encontrado para o usuário \(userId)")
        return nil
    }
    
    func updateShippingAddress(address: String) async throws {
        guard let userId = currentUser?.id else {
            throw APIError.serverError("Usuário não está logado")
        }
        
        do {
            // Criar o objeto com dados para atualização
            let userData: [String: Any] = [
                "local_envio": address
            ]
            
            print("[AuthManager] Atualizando endereço de envio para ID \(userId): \(address)")
            
            // Atualizar o perfil no servidor
            let response = try await APIService.shared.updateUserProfile(id: userId, userData: userData)
            
            // Atualizar localmente usando a chave específica
            let key = shippingAddressKey(for: userId)
            UserDefaults.standard.set(address, forKey: key)
            
            // Atualizar o cache em memória
            userAddresses[userId] = address
            
            // Remover a antiga chave genérica se existir
            UserDefaults.standard.removeObject(forKey: "userShippingAddress")
            
            // Atualizar o objeto currentUser
            if var user = currentUser {
                user = User(id: user.id, nome: user.nome, email: user.email, profileImage: user.profileImage, shippingAddress: address)
                
                // Salvar no UserDefaults
                if let userData = try? JSONEncoder().encode(user) {
                    UserDefaults.standard.set(userData, forKey: "userData")
                }
                
                await MainActor.run {
                    self.currentUser = user
                    print("[AuthManager] Endereço de envio atualizado com sucesso para userId \(userId): \(address)")
                }
            }
            
            return
        } catch {
            print("[AuthManager] Erro ao atualizar endereço de envio: \(error)")
            throw error
        }
    }
    
    /// Atualiza o usuário atual com um novo endereço
    func updateUserWithAddress(user: User, address: String) {
        var updatedUser = user
        updatedUser.shippingAddress = address
        
        // Atualiza o cache em memória e UserDefaults
        // Não precisamos verificar se id é opcional já que é um valor obrigatório no modelo User
        let userId = user.id
        userAddresses[userId] = address
        
        // Armazena no UserDefaults com a chave específica do usuário
        let key = shippingAddressKey(for: userId)
        UserDefaults.standard.set(address, forKey: key)
        
        // Atualiza o usuário atual
        DispatchQueue.main.async {
            self.currentUser = updatedUser
            print("[AuthManager] Usuário atualizado com endereço: \(address)")
        }
        
        // Salva o usuário atualizado no UserDefaults
        if let userData = try? JSONEncoder().encode(updatedUser) {
            UserDefaults.standard.set(userData, forKey: "userData")
        }
    }
    
    func register(nome: String, email: String, password: String, localEnvio: String?, completion: @escaping (Bool, String) -> Void) {
        guard !nome.isEmpty, !email.isEmpty, !password.isEmpty else {
            completion(false, "Todos os campos obrigatórios devem ser preenchidos")
            return
        }
        
        guard email.contains("@") && email.contains(".") else {
            completion(false, "Email inválido")
            return
        }
        
        guard password.count >= 6 else {
            completion(false, "Palavra-passe deve ter pelo menos 6 caracteres")
            return
        }
        
        print("[AuthManager] Iniciando registo para email: \(email)")
        
        Task {
            do {
                let result = try await APIService.shared.register(nome: nome, email: email, palavra_passe: password, local_envio: localEnvio)
                
                DispatchQueue.main.async {
                    print("[AuthManager] Registo bem-sucedido para: \(email)")
                    UserDefaults.standard.set(email, forKey: "userEmail")
                    
                    if let address = localEnvio, !address.isEmpty {
                        UserDefaults.standard.set(address, forKey: "pendingShippingAddress")
                    }
                    
                    completion(true, "Registo realizado com sucesso")
                }
            } catch {
                DispatchQueue.main.async {
                    print("[AuthManager] Falha no registo: \(error)")
                    completion(false, error.localizedDescription)
                }
            }
        }
    }
}