
import SwiftUI


struct RegisterView: View {
   
    @State private var email = ""
    @State private var password = ""
    @State private var nome = ""
    @State private var localEnvio = ""
    
    
    @State private var isLoading = false
    @State private var showAlert = false
    @State private var alertMessage = ""
    
    
    @Environment(\.dismiss) var dismiss
    
    
    var body: some View {
        
        NavigationView {
            
            Form {
               
                Section(header: Text("Nome")) {
                    TextField("Nome", text: $nome)
                }
                
                
                Section(header: Text("Email")) {
                    TextField("Email", text: $email)
                        .keyboardType(.emailAddress)     // Teclado com @ e .com
                        .autocapitalization(.none)       // Sem caps automáticas
                }
                
               
                Section(header: Text("Password")) {
                    SecureField("Password", text: $password)
                }
                

                Section(header: Text("Morada de envio (opcional)")) {
                    TextField("Morada de envio", text: $localEnvio)
                }
                
                
                Section {
                    Button(action: register) {
                        if isLoading {
                            // Indicador visual de carregamento
                            ProgressView()
                        } else {
                            // Texto normal do botão
                            Text("Criar Conta")
                        }
                    }
                    // Validação: desativa botão se campos obrigatórios vazios ou loading ativo
                    .disabled(nome.isEmpty || email.isEmpty || password.isEmpty || isLoading)
                }
            }
            // Título da tela na barra de navegação
            .navigationTitle("Criar Conta")
            
            
            .toolbar {
                ToolbarItem(placement: .cancellationAction) {
                    Button("Cancelar") { dismiss() }
                }
            }
            
            
            .alert(isPresented: $showAlert) {
                Alert(
                    title: Text("Registo"), 
                    message: Text(alertMessage), 
                    dismissButton: .default(Text("OK"))
                )
            }
        }
    }
    
    
    func register() {
        
        isLoading = true
        
       
        AuthManager.shared.register(
            nome: nome, 
            email: email, 
            password: password, 
            localEnvio: localEnvio.isEmpty ? nil : localEnvio
        ) { success, message in
            
            DispatchQueue.main.async {
                
                isLoading = false
                
                
                alertMessage = message
                showAlert = true
                
               
                if success {
                    dismiss()
                }
            }
        }
    }
}

