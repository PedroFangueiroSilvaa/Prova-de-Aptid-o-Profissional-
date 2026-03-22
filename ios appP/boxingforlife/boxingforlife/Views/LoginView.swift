
import SwiftUI


struct User: Codable {
    let id: Int                    // Identificador único do utilizador
    let nome: String              // Nome completo do utilizador
    let email: String             // Email do utilizador (usado para login)
    var profileImage: String?     // URL da imagem de perfil (opcional)
    var shippingAddress: String?  // Endereço de envio padrão (opcional)
}


struct LoginView: View {
    
    @Environment(\.dismiss) var dismiss
    
   
    @StateObject private var authManager = AuthManager.shared
    
    
    @State private var email = ""
    @State private var password = ""
    
    
    @State private var showingAlert = false
    @State private var alertMessage = ""
    @State private var isLoading = false
    
    
    var body: some View {
        // NavigationView permite navegação para outras telas
        NavigationView {
            // VStack organiza elementos verticalmente
            VStack(spacing: 20) {
                // Ícone de utilizador no topo
                Image(systemName: "person.circle.fill")
                    .resizable()
                    .scaledToFit()
                    .frame(width: 100, height: 100)
                    .foregroundColor(AppTheme.primaryOrange)
                
                // Campo de entrada para email
                TextField("Email", text: $email)
                    .textFieldStyle(RoundedBorderTextFieldStyle())
                    .autocapitalization(.none)    // Não capitalizar automaticamente
                    .keyboardType(.emailAddress)  // Teclado otimizado para email
                    .padding(.horizontal)
                    // Borda personalizada com cor laranja
                    .overlay(
                        RoundedRectangle(cornerRadius: 8)
                            .stroke(AppTheme.lightOrange, lineWidth: 1)
                    )
                
                // Campo de entrada para palavra-passe (texto oculto)
                SecureField("Palavra-passe", text: $password)
                    .textFieldStyle(RoundedBorderTextFieldStyle())
                    .padding(.horizontal)
                    // Borda personalizada com cor laranja
                    .overlay(
                        RoundedRectangle(cornerRadius: 8)
                            .stroke(AppTheme.lightOrange, lineWidth: 1)
                    )
                
                // Botão de login
                Button(action: login) {
                    if isLoading {
                        // Mostra indicador de carregamento durante o login
                        ProgressView()
                            .progressViewStyle(CircularProgressViewStyle(tint: .white))
                    } else {
                        // Texto normal do botão
                        Text("Entrar")
                            .foregroundColor(.white)
                            .frame(maxWidth: .infinity)
                            .padding()
                    }
                }
                .background(AppTheme.primaryOrange)
                .cornerRadius(AppTheme.buttonCornerRadius)
                .disabled(isLoading)  // Desativa botão durante carregamento
                
                // Link para tela de registo
                NavigationLink(destination: RegisterView()) {
                    Text("Criar conta")
                        .foregroundColor(AppTheme.primaryOrange)
                }
            }
            .padding()
            .navigationTitle("Login")
            // Configurações da NavigationView
            .navigationBarTitleDisplayMode(.inline)  // Título compacto na barra de navegação
            .tint(AppTheme.primaryOrange)            // Cor laranja para elementos interativos
            
           
            .alert("Erro", isPresented: $showingAlert) {
                Button("OK", role: .cancel) { }
            } message: {
                Text(alertMessage)
            }
        }
    }
    
    
    private func login() {
       
        guard !email.isEmpty && !password.isEmpty else {
            alertMessage = "Por favor, preencha todos os campos"
            showingAlert = true
            return
        }
        
        
        isLoading = true
        
        
        let sessionId = UserDefaults.standard.string(forKey: "cartSessionId") ?? "none"
        print("🔄 Iniciando processo de login")
        print("📝 Session ID atual: \(sessionId)")
        
        
        Task {
            do {
                
                let result = try await APIService.shared.login(email: email, palavra_passe: password)
                print("✅ Resposta do login: \(result)")
                
                
                if let success = result["success"] as? Bool, success {
                    
                    if let userId = result["id"] as? Int,
                       let userName = result["nome"] as? String {
                        print("👤 Login bem sucedido. UserID: \(userId), Nome: \(userName)")
                        
                       
                        let cartItems = try await APIService.shared.getCartBySession(sessionId: sessionId)
                        print("🛒 Itens encontrados no carrinho da sessão: \(cartItems.count)")
                        
                        if !cartItems.isEmpty {
                            print("📦 Transferindo \(cartItems.count) itens para o usuário \(userId)...")
                            try await APIService.shared.transferCart(fromSessionId: sessionId, toUserId: userId)
                            print("✨ Transferência do carrinho concluída com sucesso!")
                        } else {
                            print("ℹ️ Nenhum item para transferir do carrinho da sessão")
                        }
                        
                        
                        await MainActor.run {
                            authManager.login(userId: userId, userName: userName)
                            dismiss()
                        }
                        print("✅ Processo de login finalizado com sucesso")
                    } else {
                        
                        print("❌ Dados do usuário não encontrados na resposta: \(result)")
                        throw APIError.serverError("Dados do usuário não encontrados")
                    }
                } else {
                    
                    let errorMessage = result["message"] as? String ?? "Erro desconhecido"
                    print("❌ Erro no login: \(errorMessage)")
                    throw APIError.serverError(errorMessage)
                }
            } catch {
                
                print("❌ Erro durante o processo: \(error)")
                
                
                await MainActor.run {
                    alertMessage = error.localizedDescription
                    showingAlert = true
                }
            }
            
            
            await MainActor.run {
                isLoading = false
            }
        }
    }
}


#Preview {
    LoginView()
}