// Import do framework SwiftUI - necessário para usar todos os componentes de UI do SwiftUI
import SwiftUI
// Import do PhotosUI - framework para acesso à galeria de fotos do utilizador
import PhotosUI
// Import do UniformTypeIdentifiers - usado para definir tipos de ficheiros (ex: imagens)
import UniformTypeIdentifiers


struct CreateBlogPostView: View {
    // MARK: - Environment Objects e Propriedades do Sistema
    
    // Environment value que permite fechar/dismissar a vista modal
    @Environment(\.dismiss) private var dismiss
    
    // EnvironmentObject que fornece acesso ao AuthManager partilhado
    // Contém informações do utilizador logado e estado de autenticação
    @EnvironmentObject private var authManager: AuthManager
    
    // MARK: - Estado do Formulário (Form State)
    
    // Texto do título do blog (binding para TextField)
    @State private var title: String = ""
    
    // Conteúdo/corpo do blog (binding para TextEditor)
    @State private var content: String = ""
    
    // Imagem selecionada pelo utilizador (opcional)
    @State private var selectedImage: UIImage?
    
    // Item selecionado do PhotosPicker (para conversão para UIImage)
    @State private var selectedItem: PhotosPickerItem?
    
    // MARK: - Estado de UI e Loading
    
    // Flag que indica se a submissão está em progresso
    // Controla exibição de loading e desabilita controles
    @State private var isLoading = false
    
    // Flag para mostrar alertas de erro
    @State private var showError = false
    
    // Mensagem de erro para exibir no alerta
    @State private var errorMessage = ""
    
    // Flag para mostrar mensagem de sucesso após publicação
    @State private var showSuccessMessage = false
    
    // Flag que controla a exibição do DocumentPicker para seleção de ficheiros
    @State private var showDocumentPicker = false
    
    // MARK: - Propriedades de Design
    
    // Cor primária da aplicação (vermelho do boxe) usada em elementos de destaque
    // Valores RGB: 80% vermelho, 20% verde, 20% azul
    private let primaryColor = Color(red: 0.8, green: 0.2, blue: 0.2) // Vermelho boxe
    
    var body: some View {
        // NavigationView fornece contexto de navegação para a vista modal
        NavigationView {
            // Form cria um formulário com estilo nativo iOS (células agrupadas)
            Form {
                // MARK: - Secções do Formulário
                
                // Seção para entrada do título do blog
                titleSection
                
                // Seção para entrada do conteúdo/corpo do blog
                contentSection
                
                // Seção para seleção e preview de imagem
                imageSection
                
                // Seção com botão para publicar o blog
                publishButtonSection
            }
            // MARK: - Configuração da Navegação Modal
            
            // Define o título da vista modal
            .navigationTitle("Criar Blog")
            
            // Configura botões na barra de navegação
            .toolbar {
                // ToolbarItem para o lado direito da barra
                ToolbarItem(placement: .navigationBarTrailing) {
                    // Botão para cancelar e fechar a vista modal
                    Button("Cancelar") {
                        // Fecha a vista modal
                        dismiss()
                    }
                }
            }
            
            // MARK: - Gestão de Alertas
            
            // Alerta para mostrar erros durante o processo
            .alert("Erro", isPresented: $showError) {
                // Botão único para fechar o alerta
                Button("OK", role: .cancel) {}
            } message: {
                // Mensagem do erro específico
                Text(errorMessage)
            }
            
            // Alerta para mostrar mensagem de sucesso após publicação
            .alert("Sucesso", isPresented: $showSuccessMessage) {
                // Botão que fecha a vista modal após sucesso
                Button("OK") {
                    // Fecha a vista modal
                    dismiss()
                }
            } message: {
                // Mensagem de confirmação de sucesso
                Text("Seu blog foi publicado com sucesso!")
            }
            
            // MARK: - Configurações de Estado e Interatividade
            
            // Desabilita toda a interface durante o processo de loading
            .disabled(isLoading)
            
            // Sheet modal para seleção de documentos/ficheiros
            .sheet(isPresented: $showDocumentPicker) {
                // Componente personalizado para seleção de ficheiros
                DocumentPicker(selectedImage: $selectedImage)
            }
            
            // MARK: - Observadores de Mudanças
            
            // Observa mudanças no selectedItem do PhotosPicker
            .onChange(of: selectedItem) { newValue in
                // Quando um novo item é selecionado, carrega a imagem
                if let item = newValue {
                    loadImage(from: item)
                }
            }
        }
    }
    
   
    private var titleSection: some View {
        // Section cria uma seção agrupada no formulário com cabeçalho
        Section(header: Text("Título do Blog")) {
            // TextField permite entrada de texto numa linha
            // Binding para a variável title permite atualizações automáticas
            TextField("Digite um título atrativo", text: $title)
        }
    }
    
   
    private var contentSection: some View {
        Section(header: Text("Conteúdo")) {
            // TextEditor permite entrada de texto multi-linha com scroll
            TextEditor(text: $content) // Binding para a variável content
                .frame(minHeight: 150) // Define altura mínima de 150 pontos
        }
    }
    
    
    private var imageSection: some View {
        Section(header: Text("Imagem do Blog")) {
            // VStack organiza preview e botões verticalmente
            VStack {
                // Componente que mostra preview da imagem selecionada
                imagePreview
                
                // Botões para seleção de imagem (galeria e ficheiros)
                imageSelectionButton
            }
        }
    }
    
    
    private var imagePreview: some View {
        // Group permite retornar diferentes vistas baseado numa condição
        Group {
            // Condicional que verifica se há imagem selecionada
            if let image = selectedImage {
                // MARK: - Preview da Imagem Selecionada
                // Mostra a imagem com formatação adequada
                Image(uiImage: image)
                    .resizable() // Permite redimensionar a imagem
                    .aspectRatio(contentMode: .fit) // Mantém proporção, ajusta ao container
                    .frame(height: 200) // Define altura fixa de 200 pontos
                    .cornerRadius(8) // Bordas arredondadas para design moderno
            } else {
                // MARK: - Placeholder quando não há imagem
                // Retângulo cinzento com texto explicativo
                RoundedRectangle(cornerRadius: 8)
                    .fill(Color.gray.opacity(0.2)) // Preenchimento cinzento claro
                    .frame(height: 200) // Mesma altura que o preview de imagem
                    .overlay(
                        // Texto sobreposto ao retângulo
                        Text("Selecione uma imagem")
                            .foregroundColor(.gray) // Cor cinzenta para o texto
                    )
            }
        }
    }
    
    
    private var imageSelectionButton: some View {
        // HStack organiza os dois botões horizontalmente
        HStack {
            // MARK: - Botão da Galeria de Fotos
            // PhotosPicker é o componente nativo para acesso à galeria
            PhotosPicker(
                selection: $selectedItem, // Binding para o item selecionado
                matching: .images, // Filtra apenas imagens
                photoLibrary: .shared() // Usa a biblioteca de fotos partilhada
            ) {
                // Label combina texto e ícone
                Label("Galeria de Fotos", systemImage: "photo")
                    .frame(maxWidth: .infinity) // Ocupa toda a largura disponível
                    .padding() // Padding interno para aumentar área tocável
                    .background(primaryColor) // Fundo com cor primária (vermelho)
                    .foregroundColor(.white) // Texto branco para contraste
                    .cornerRadius(8) // Bordas arredondadas
            }
            
            // MARK: - Botão de Seleção de Ficheiros
            // Botão personalizado para abrir DocumentPicker
            Button(action: {
                // Ativa a exibição do DocumentPicker
                showDocumentPicker = true
            }) {
                Label("Arquivos", systemImage: "folder")
                    .frame(maxWidth: .infinity) // Ocupa toda a largura disponível
                    .padding() // Padding interno
                    .background(Color.gray) // Fundo cinzento para diferenciação
                    .foregroundColor(.white) // Texto branco
                    .cornerRadius(8) // Bordas arredondadas
            }
        }
    }
    
   
    private var publishButtonSection: some View {
        Section {
            // Botão principal para submeter o blog
            Button(action: {
                // Task cria contexto assíncrono para chamar submitPost()
                Task {
                    await submitPost()
                }
            }) {
                // Conteúdo do botão (texto ou loading)
                publishButtonContent
            }
            // MARK: - Configurações do Botão
            // Desabilita se campos obrigatórios vazios ou durante loading
            .disabled(title.isEmpty || content.isEmpty || isLoading)
            // Remove estilo padrão do botão para customização total
            .buttonStyle(BorderlessButtonStyle())
            // Define fundo da linha com cor primária
            .listRowBackground(primaryColor)
            // Texto branco para contraste com fundo vermelho
            .foregroundColor(.white)
        }
    }
    
    
    private var publishButtonContent: some View {
        // Group permite retornar diferentes vistas baseado numa condição
        Group {
            // Condicional baseado no estado de loading
            if isLoading {
                // MARK: - Estado de Loading
                // ProgressView mostra indicador de carregamento circular
                ProgressView()
                    .progressViewStyle(CircularProgressViewStyle()) // Estilo circular
                    .frame(maxWidth: .infinity) // Centraliza horizontalmente
            } else {
                // MARK: - Estado Normal
                // Texto normal do botão
                Text("Publicar Blog")
                    .frame(maxWidth: .infinity) // Ocupa toda a largura
            }
        }
    }
    
  
    private func loadImage(from item: PhotosPickerItem?) {
        // Validação: verifica se o item existe
        guard let item = item else { return }
        
        // MARK: - Carregamento Assíncrono da Imagem
        
        // loadTransferable carrega o item como Data de forma assíncrona
        item.loadTransferable(type: Data.self) { result in
            // Switch para tratar o resultado (sucesso ou falha)
            switch result {
            case .success(let data):
                // MARK: - Processamento bem-sucedido
                // Verifica se os dados existem e podem ser convertidos para UIImage
                if let data = data, let image = UIImage(data: data) {
                    // DispatchQueue.main.async garante que a UI é atualizada na main thread
                    DispatchQueue.main.async {
                        // Define a imagem selecionada (atualiza a UI)
                        self.selectedImage = image
                    }
                }
            case .failure(let error):
                // MARK: - Tratamento de erro
                // Log do erro (em produção deveria mostrar feedback ao utilizador)
                print("Erro ao carregar imagem: \(error)")
            }
        }
    }
    
   
    private func submitPost() async {
        // MARK: - Validação de Autenticação
        
        // Obtém o ID do utilizador do UserDefaults (sistema de autenticação local)
        // TODO: Integrar com AuthManager para método mais robusto
        let userId = UserDefaults.standard.integer(forKey: "userId")
        
        // Verifica se o utilizador está logado (ID > 0)
        guard userId > 0 else {
            // Se não logado, mostra erro e retorna
            showError = true
            errorMessage = "Você precisa estar logado para publicar um blog."
            return
        }
        
        // MARK: - Início do Processo de Submissão
        
        // Ativa indicador de loading
        isLoading = true
        
        // MARK: - Preparação da Imagem (Opcional)
        
        // Variável para armazenar dados da imagem comprimida
        var imageData: Data?
        
        // Se há imagem selecionada, prepara os dados
        if let image = selectedImage {
            // Converte UIImage para Data JPEG com compressão de 70%
            // Isso reduz o tamanho do ficheiro para upload mais rápido
            if let compressedData = image.jpegData(compressionQuality: 0.7) {
                imageData = compressedData
            }
        }
        
        
        do {
            // Chama APIService para criar o blog post no servidor
            // Passa título, conteúdo, dados da imagem (opcional) e ID do utilizador
            let _ = try await APIService.shared.createBlogPost(
                title: title,
                content: content,
                image: imageData,
                userId: userId
            )
            
            
            // MainActor.run garante que as atualizações da UI acontecem na main thread
            await MainActor.run {
                isLoading = false // Desativa loading
                showSuccessMessage = true // Mostra mensagem de sucesso
            }
        }
        catch {
            // Em caso de erro, atualiza a UI na main thread
            await MainActor.run {
                isLoading = false // Desativa loading
                showError = true // Ativa alerta de erro
                // Define mensagem de erro com descrição do erro
                errorMessage = "Erro ao publicar o blog: \(error.localizedDescription)"
            }
        }
    }
}


struct DocumentPicker: UIViewControllerRepresentable {
    
    // Binding para a imagem selecionada (permite atualizar a vista pai)
    @Binding var selectedImage: UIImage?
    
    
   
    func makeUIViewController(context: Context) -> UIDocumentPickerViewController {
        // Cria picker configurado para abrir imagens, copiando ficheiros
        let picker = UIDocumentPickerViewController(forOpeningContentTypes: [UTType.image], asCopy: true)
        // Define o coordinator como delegate para receber callbacks
        picker.delegate = context.coordinator
        // Permite apenas seleção de um ficheiro de cada vez
        picker.allowsMultipleSelection = false
        return picker
    }
    
    
    func updateUIViewController(_ uiViewController: UIDocumentPickerViewController, context: Context) {}
    
   
    func makeCoordinator() -> Coordinator {
        return Coordinator(parent: self)
    }
    
   
    class Coordinator: NSObject, UIDocumentPickerDelegate {
        // MARK: - Propriedades
        
        // Referência para o DocumentPicker pai (weak reference implícita)
        let parent: DocumentPicker
        
        // MARK: - Inicializador
        
       
        init(parent: DocumentPicker) {
            self.parent = parent
        }
        
       
        func documentPicker(_ controller: UIDocumentPickerViewController, didPickDocumentsAt urls: [URL]) {
            // MARK: - Validação da Seleção
            
            // Obtém o primeiro (e único) URL selecionado
            guard let url = urls.first else { return }
            
            // MARK: - Acesso de Segurança ao Ficheiro
            
            // Inicia acesso de segurança ao ficheiro (necessário para sandbox)
            guard url.startAccessingSecurityScopedResource() else {
                print("Erro ao acessar arquivo")
                return
            }
            
            // defer garante que o acesso é terminado quando a função termina
            defer { url.stopAccessingSecurityScopedResource() }
            
            
            do {
                // Carrega os dados do ficheiro
                let data = try Data(contentsOf: url)
                
                // Tenta converter os dados para UIImage
                if let image = UIImage(data: data) {
                    // DispatchQueue.main.async garante atualização na main thread
                    DispatchQueue.main.async {
                        // Atualiza a imagem selecionada no DocumentPicker pai
                        self.parent.selectedImage = image
                    }
                }
            }
            // MARK: - Tratamento de Erros
            catch {
                // Log do erro (em produção deveria mostrar feedback ao utilizador)
                print("Erro ao carregar imagem do documento: \(error)")
            }
        }
    }
}