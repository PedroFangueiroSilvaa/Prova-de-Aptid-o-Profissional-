

import SwiftUI

struct ProfileView: View {
    // Acesso ao gestor de autenticação
    @EnvironmentObject var authManager: AuthManager
    
    // Estados para controlar modais e alertas
    @State private var showingEditProfile = false
    @State private var showingChangePassword = false
    @State private var showingOrderHistory = false
    @State private var showingLogoutAlert = false
    @State private var showingDeleteAccountAlert = false
    @State private var showingEditShippingAddress = false
    
    // Estados para gestão de carregamento e erros
    @State private var isLoading = false
    @State private var errorMessage: String?
    @State private var showingError = false
    
    // Constrói a interface visual da tela do perfil
    var body: some View {
        // NavigationView permite título e navegação
        NavigationView {
            // Lista com diferentes secções do perfil
            List {
                // Secção do cabeçalho com foto e nome do utilizador
                profileHeaderSection
                
                // Secção para gerir endereço de envio
                shippingAddressSection
                
                // Secção com configurações da conta
                accountSettingsSection
                
                // Secção para gestão de blogs do utilizador
                blogManagementSection
                
                // Secção com preferências da aplicação
                preferencesSection
                
                // Secção de suporte e ajuda
                supportSection
                
                // Secção com ações perigosas (logout, eliminar conta)
                dangerZoneSection
            }
            .listStyle(InsetGroupedListStyle())
            .navigationTitle("Perfil")
            
            // Alert de confirmação para logout
            .alert("Terminar Sessão", isPresented: $showingLogoutAlert) {
                Button("Cancelar", role: .cancel) { }
                Button("Terminar Sessão", role: .destructive) {
                    authManager.signOut()
                }
            } message: {
                Text("Tem a certeza que pretende terminar a sessão?")
            }
            
            // Alert de confirmação para eliminar conta
            .alert("Eliminar Conta", isPresented: $showingDeleteAccountAlert) {
                Button("Cancelar", role: .cancel) { }
                Button("Eliminar", role: .destructive) {
                    deleteAccount()
                }
            } message: {
                Text("Esta ação não pode ser desfeita. Todos os seus dados serão permanentemente eliminados.")
            }
            
            // Alert para mostrar erros
            .alert("Erro", isPresented: $showingError) {
                Button("OK", role: .cancel) { }
            } message: {
                Text(errorMessage ?? "Ocorreu um erro inesperado")
            }
            
            // Modais para diferentes funcionalidades
            .sheet(isPresented: $showingEditProfile) {
                EditProfileView()
            }
            .sheet(isPresented: $showingChangePassword) {
                ChangePasswordView()
            }
            .sheet(isPresented: $showingEditShippingAddress) {
                EditShippingAddressView()
            }
            
            // Overlay de carregamento
            .overlay {
                if isLoading {
                    ProgressView()
                        .scaleEffect(1.5)
                        .frame(maxWidth: .infinity, maxHeight: .infinity)
                        .background(Color.black.opacity(0.2))
                }
            }
            
            // Carrega dados do perfil quando a tela aparece
            .onAppear {
                Task {
                    await loadUserProfile()
                }
            }
        }
    }
    
    // Função que carrega dados atualizados do perfil do servidor
    private func loadUserProfile() async {
        // Verifica se há utilizador logado
        guard let user = authManager.currentUser else { return }
        
        do {
            // Busca dados atualizados do servidor
            let userData = try await APIService.shared.getUserProfile(id: user.id)
            
            // Extrai informações ou usa valores existentes como fallback
            let nome = userData["nome"] as? String ?? user.nome
            let email = userData["email"] as? String ?? user.email
            let shippingAddress = userData["local_envio"] as? String ?? user.shippingAddress
            
            // Cria objeto User atualizado
            let updatedUser = User(id: user.id, nome: nome, email: email, profileImage: user.profileImage, shippingAddress: shippingAddress)
            
            // Atualiza na thread principal (UI)
            await MainActor.run {
                authManager.currentUser = updatedUser
            }
        } catch {
            // Em caso de erro, mostra alerta
            await MainActor.run {
                errorMessage = error.localizedDescription
                showingError = true
            }
        }
    }
    
    private var profileHeaderSection: some View {
        Section {
            HStack {
                if let user = authManager.currentUser {
                    AsyncImage(url: URL(string: user.profileImage ?? "")) { image in
                        image
                            .resizable()
                            .scaledToFill()
                    } placeholder: {
                        Image(systemName: "person.circle.fill")
                            .resizable()
                            .foregroundColor(.gray)
                    }
                    .frame(width: 80, height: 80)
                    .clipShape(Circle())
                    .overlay(Circle().stroke(Color.gray.opacity(0.2), lineWidth: 2))
                    
                    VStack(alignment: .leading, spacing: 4) {
                        Text(user.nome)
                            .font(.headline)
                        Text(user.email)
                            .font(.subheadline)
                            .foregroundColor(.gray)
                    }
                    .padding(.leading)
                }
            }
            .padding(.vertical, 8)
        }
    }
    
    private var shippingAddressSection: some View {
        Section(header: Text("Endereço de Envio")) {
            if let address = authManager.getUserShippingAddress(), !address.isEmpty {
                VStack(alignment: .leading, spacing: 4) {
                    Text(address)
                        .foregroundColor(.gray)
                    Button("Alterar Endereço") {
                        showingEditShippingAddress = true
                    }
                    .foregroundColor(.blue)
                    .padding(.top, 4)
                }
            } else {
                HStack {
                    Text("Nenhum endereço cadastrado")
                        .foregroundColor(.gray)
                    Spacer()
                    Button("Adicionar") {
                        showingEditShippingAddress = true
                    }
                    .foregroundColor(.blue)
                }
            }
        }
    }
    
    private var accountSettingsSection: some View {
        Section(header: Text("Configurações da Conta")) {
            Button(action: { showingEditProfile = true }) {
                Label("Editar Perfil", systemImage: "person.fill")
            }
            
            Button(action: { showingChangePassword = true }) {
                Label("Alterar Senha", systemImage: "lock.fill")
            }
            
            NavigationLink(destination: OrdersView()) {
                Label("Histórico de Encomendas", systemImage: "clock.fill")
            }
        }
    }
    
    private var blogManagementSection: some View {
        Section(header: Text("Gestão de Conteúdo")) {
            NavigationLink(destination: UserBlogsView()) {
                Label("Meus Blogs", systemImage: "doc.text.fill")
            }
            
            NavigationLink(destination: UserCommentsView()) {
                Label("Meus Comentários", systemImage: "bubble.left.and.bubble.right.fill")
            }
        }
    }
    
    private var preferencesSection: some View {
        Section(header: Text("Preferências")) {
            NavigationLink(destination: NotificationSettingsView()) {
                Label("Notificações", systemImage: "bell.fill")
            }
            
            NavigationLink(destination: PrivacySettingsView()) {
                Label("Privacidade", systemImage: "hand.raised.fill")
            }
            
            NavigationLink(destination: LanguageSettingsView()) {
                Label("Idioma", systemImage: "globe")
            }
        }
    }
    
    private var supportSection: some View {
        Section(header: Text("Suporte")) {
            NavigationLink(destination: FAQView()) {
                Label("FAQ", systemImage: "questionmark.circle.fill")
            }
            
            NavigationLink(destination: ContactSupportView()) {
                Label("Contactar Suporte", systemImage: "envelope.fill")
            }
            
            Link(destination: URL(string: "https://boxingforlife.pt/termos")!) {
                Label("Termos e Condições", systemImage: "doc.text.fill")
            }
            
            Link(destination: URL(string: "https://boxingforlife.pt/privacidade")!) {
                Label("Política de Privacidade", systemImage: "shield.fill")
            }
        }
    }
    
    private var dangerZoneSection: some View {
        Section {
            Button(action: { showingDeleteAccountAlert = true }) {
                Label("Eliminar Conta", systemImage: "trash.fill")
                    .foregroundColor(.red)
            }
            
            Button(action: { showingLogoutAlert = true }) {
                Label("Terminar Sessão", systemImage: "arrow.right.square.fill")
                    .foregroundColor(.red)
            }
        }
    }
    
    private func deleteAccount() {
        isLoading = true
        Task {
            do {
                try await authManager.signOut()
                // Note: Actual account deletion should be implemented in AuthManager
                authManager.signOut()
            } catch {
                errorMessage = error.localizedDescription
                showingError = true
            }
            isLoading = false
        }
    }
}

// MARK: - Edit Profile View
struct EditProfileView: View {
    @Environment(\.dismiss) private var dismiss
    @EnvironmentObject var authManager: AuthManager
    @State private var name = ""
    @State private var email = ""
    @State private var originalName = ""
    @State private var originalEmail = ""
    @State private var showingImagePicker = false
    @State private var selectedImage: UIImage?
    @State private var isLoading = false
    @State private var errorMessage: String?
    @State private var showingError = false
    
    var body: some View {
        NavigationView {
            Form {
                Section(header: Text("Foto de Perfil")) {
                    HStack {
                        Spacer()
                        Button(action: { showingImagePicker = true }) {
                            if let image = selectedImage {
                                Image(uiImage: image)
                                    .resizable()
                                    .scaledToFill()
                                    .frame(width: 100, height: 100)
                                    .clipShape(Circle())
                            } else {
                                Image(systemName: "person.circle.fill")
                                    .resizable()
                                    .frame(width: 100, height: 100)
                                    .foregroundColor(.gray)
                            }
                        }
                        Spacer()
                    }
                }
                
                Section(header: Text("Informações Pessoais")) {
                    TextField("Nome", text: $name)
                    TextField("Email", text: $email)
                        .textContentType(.emailAddress)
                        .keyboardType(.emailAddress)
                        .autocapitalization(.none)
                }
            }
            .navigationTitle("Editar Perfil")
            .navigationBarItems(
                leading: Button("Cancelar") { dismiss() },
                trailing: Button("Guardar") { saveProfile() }
                    .disabled(isLoading || (!hasChanges))
            )
            .sheet(isPresented: $showingImagePicker) {
                ImagePicker(image: $selectedImage)
            }
            .alert("Erro", isPresented: $showingError) {
                Button("OK", role: .cancel) { }
            } message: {
                Text(errorMessage ?? "Ocorreu um erro inesperado")
            }
            .overlay {
                if isLoading {
                    ProgressView()
                        .scaleEffect(1.5)
                        .frame(maxWidth: .infinity, maxHeight: .infinity)
                        .background(Color.black.opacity(0.2))
                }
            }
            .onAppear {
                if let user = authManager.currentUser {
                    name = user.nome
                    email = user.email
                    originalName = user.nome
                    originalEmail = user.email
                }
            }
        }
    }
    
    private var hasChanges: Bool {
        name.trimmingCharacters(in: .whitespacesAndNewlines) != originalName.trimmingCharacters(in: .whitespacesAndNewlines) ||
        email.trimmingCharacters(in: .whitespacesAndNewlines) != originalEmail.trimmingCharacters(in: .whitespacesAndNewlines)
    }
    
    private func saveProfile() {
        isLoading = true
        Task {
            do {
                guard let user = authManager.currentUser else { return }
                // Atualiza localmente
                try await authManager.updateProfile(name: name, email: email)
                // Atualiza no backend
                var userData: [String: Any] = [:]
                if name.trimmingCharacters(in: .whitespacesAndNewlines) != originalName.trimmingCharacters(in: .whitespacesAndNewlines) {
                    userData["nome"] = name
                }
                if email.trimmingCharacters(in: .whitespacesAndNewlines) != originalEmail.trimmingCharacters(in: .whitespacesAndNewlines) {
                    userData["email"] = email
                }
                if userData.isEmpty {
                    await MainActor.run {
                        isLoading = false
                        dismiss()
                    }
                    return
                }
                _ = try await APIService.shared.updateUserProfile(id: user.id, userData: userData)
                await MainActor.run {
                    isLoading = false
                    dismiss()
                }
            } catch {
                await MainActor.run {
                    errorMessage = error.localizedDescription
                    showingError = true
                    isLoading = false
                }
            }
        }
    }
}

// MARK: - Change Password View
struct ChangePasswordView: View {
    @Environment(\.dismiss) private var dismiss
    @State private var currentPassword = ""
    @State private var newPassword = ""
    @State private var confirmPassword = ""
    @State private var isLoading = false
    @State private var errorMessage: String?
    @State private var showingError = false
    
    var body: some View {
        NavigationView {
            Form {
                Section(header: Text("Senha Atual")) {
                    SecureField("Senha Atual", text: $currentPassword)
                }
                
                Section(header: Text("Nova Senha")) {
                    SecureField("Nova Senha", text: $newPassword)
                    SecureField("Confirmar Nova Senha", text: $confirmPassword)
                }
            }
            .navigationTitle("Alterar Senha")
            .navigationBarItems(
                leading: Button("Cancelar") { dismiss() },
                trailing: Button("Guardar") { changePassword() }
                    .disabled(isLoading || !isFormValid)
            )
            .alert("Erro", isPresented: $showingError) {
                Button("OK", role: .cancel) { }
            } message: {
                Text(errorMessage ?? "Ocorreu um erro inesperado")
            }
            .overlay {
                if isLoading {
                    ProgressView()
                        .scaleEffect(1.5)
                        .frame(maxWidth: .infinity, maxHeight: .infinity)
                        .background(Color.black.opacity(0.2))
                }
            }
        }
    }
    
    private var isFormValid: Bool {
        !currentPassword.isEmpty &&
        !newPassword.isEmpty &&
        !confirmPassword.isEmpty &&
        newPassword == confirmPassword &&
        newPassword.count >= 6
    }
    
    private func changePassword() {
        isLoading = true
        // Implement password change logic here
        dismiss()
    }
}

// MARK: - Edit Shipping Address View
struct EditShippingAddressView: View {
    @Environment(\.dismiss) private var dismiss
    @EnvironmentObject var authManager: AuthManager
    @State private var address = ""
    @State private var isLoading = false
    @State private var errorMessage: String?
    @State private var showingError = false
    
    var body: some View {
        NavigationView {
            Form {
                Section(header: Text("Endereço de Envio")) {
                    TextField("Endereço completo", text: $address)
                        .onAppear {
                            // Carregar o endereço atual, se existir
                            address = authManager.getUserShippingAddress() ?? ""
                        }
                }
                
                Section {
                    Text("Insira seu endereço completo incluindo rua, número, complemento, bairro, cidade, CEP e país.")
                        .font(.caption)
                        .foregroundColor(.gray)
                }
            }
            .navigationTitle("Endereço de Envio")
            .navigationBarItems(
                leading: Button("Cancelar") { dismiss() },
                trailing: Button("Salvar") { saveAddress() }
                    .disabled(isLoading || address.isEmpty)
            )
            .alert("Erro", isPresented: $showingError) {
                Button("OK", role: .cancel) { }
            } message: {
                Text(errorMessage ?? "Ocorreu um erro inesperado")
            }
            .overlay {
                if isLoading {
                    ProgressView()
                        .scaleEffect(1.5)
                        .frame(maxWidth: .infinity, maxHeight: .infinity)
                        .background(Color.black.opacity(0.2))
                }
            }
        }
    }
    
    private func saveAddress() {
        isLoading = true
        Task {
            do {
                try await authManager.updateShippingAddress(address: address)
                await MainActor.run {
                    dismiss()
                }
            } catch {
                await MainActor.run {
                    errorMessage = error.localizedDescription
                    showingError = true
                    isLoading = false
                }
            }
        }
    }
}

// MARK: - Image Picker
struct ImagePicker: UIViewControllerRepresentable {
    @Binding var image: UIImage?
    @Environment(\.dismiss) private var dismiss
    
    func makeUIViewController(context: Context) -> UIImagePickerController {
        let picker = UIImagePickerController()
        picker.delegate = context.coordinator
        picker.sourceType = .photoLibrary
        return picker
    }
    
    func updateUIViewController(_ uiViewController: UIImagePickerController, context: Context) {}
    
    func makeCoordinator() -> Coordinator {
        Coordinator(self)
    }
    
    class Coordinator: NSObject, UIImagePickerControllerDelegate, UINavigationControllerDelegate {
        let parent: ImagePicker
        
        init(_ parent: ImagePicker) {
            self.parent = parent
        }
        
        func imagePickerController(_ picker: UIImagePickerController, didFinishPickingMediaWithInfo info: [UIImagePickerController.InfoKey : Any]) {
            if let image = info[.originalImage] as? UIImage {
                parent.image = image
            }
            parent.dismiss()
        }
    }
}

// MARK: - Supporting Views
struct NotificationSettingsView: View {
    @AppStorage("pushNotifications") private var pushNotifications = true
    @AppStorage("emailNotifications") private var emailNotifications = true
    @AppStorage("orderUpdates") private var orderUpdates = true
    @AppStorage("promotions") private var promotions = false
    
    var body: some View {
        Form {
            Section(header: Text("Notificações Push")) {
                Toggle("Ativar Notificações Push", isOn: $pushNotifications)
                if pushNotifications {
                    Toggle("Atualizações de Encomendas", isOn: $orderUpdates)
                    Toggle("Promoções e Ofertas", isOn: $promotions)
                }
            }
            
            Section(header: Text("Notificações por Email")) {
                Toggle("Ativar Notificações por Email", isOn: $emailNotifications)
            }
        }
        .navigationTitle("Notificações")
    }
}

struct PrivacySettingsView: View {
    @AppStorage("shareActivity") private var shareActivity = false
    @AppStorage("showOnlineStatus") private var showOnlineStatus = true
    
    var body: some View {
        Form {
            Section(header: Text("Privacidade")) {
                Toggle("Partilhar Atividade", isOn: $shareActivity)
                Toggle("Mostrar Estado Online", isOn: $showOnlineStatus)
            }
            
            Section(header: Text("Dados Pessoais")) {
                NavigationLink("Gerir Dados Pessoais") {
                    PersonalDataManagementView()
                }
            }
        }
        .navigationTitle("Privacidade")
    }
}

struct LanguageSettingsView: View {
    @AppStorage("language") private var language = "pt"
    
    let languages = [
        "pt": "Português",
        "en": "English",
        "es": "Español"
    ]
    
    var body: some View {
        Form {
            Section {
                Picker("Idioma", selection: $language) {
                    ForEach(Array(languages.keys.sorted()), id: \.self) { key in
                        Text(languages[key] ?? "").tag(key)
                    }
                }
            }
        }
        .navigationTitle("Idioma")
    }
}

struct FAQView: View {
    let faqs = [
        FAQ(question: "Como posso alterar a minha senha?",
            answer: "Para alterar a sua senha, vá ao seu perfil e selecione 'Alterar Senha'. Siga as instruções fornecidas."),
        FAQ(question: "Como posso rastrear a minha encomenda?",
            answer: "Pode rastrear a sua encomenda através do histórico de encomendas no seu perfil."),
        FAQ(question: "Qual é a política de devolução?",
            answer: "Aceitamos devoluções dentro de 14 dias após a entrega. O produto deve estar em condições originais.")
    ]
    
    var body: some View {
        List(faqs) { faq in
            VStack(alignment: .leading, spacing: 8) {
                Text(faq.question)
                    .font(.headline)
                Text(faq.answer)
                    .font(.subheadline)
                    .foregroundColor(.gray)
            }
            .padding(.vertical, 4)
        }
        .navigationTitle("FAQ")
    }
}

struct ContactSupportView: View {
    @State private var subject = ""
    @State private var message = ""
    @State private var showingSuccessAlert = false
    
    var body: some View {
        Form {
            Section(header: Text("Assunto")) {
                TextField("Assunto", text: $subject)
            }
            
            Section(header: Text("Mensagem")) {
                TextEditor(text: $message)
                    .frame(height: 150)
            }
            
            Section {
                Button("Enviar") {
                    // Implement send message logic
                    showingSuccessAlert = true
                }
                .disabled(subject.isEmpty || message.isEmpty)
            }
        }
        .navigationTitle("Contactar Suporte")
        .alert("Mensagem Enviada", isPresented: $showingSuccessAlert) {
            Button("OK", role: .cancel) { }
        } message: {
            Text("A sua mensagem foi enviada com sucesso. Entraremos em contacto em breve.")
        }
    }
}

struct PersonalDataManagementView: View {
    var body: some View {
        List {
            Section(header: Text("Dados Pessoais")) {
                NavigationLink("Exportar Dados") {
                    Text("Funcionalidade em desenvolvimento")
                }
                
                NavigationLink("Eliminar Dados") {
                    Text("Funcionalidade em desenvolvimento")
                }
            }
            
            Section(header: Text("Preferências")) {
                NavigationLink("Gerir Preferências") {
                    Text("Funcionalidade em desenvolvimento")
                }
            }
        }
        .navigationTitle("Dados Pessoais")
    }
}

struct FAQ: Identifiable {
    let id = UUID()
    let question: String
    let answer: String
}

#Preview {
    ProfileView()
        .environmentObject(AuthManager())
}