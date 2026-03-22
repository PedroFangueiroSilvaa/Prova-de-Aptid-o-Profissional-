// Import do framework SwiftUI - necessário para usar todos os componentes de UI do SwiftUI
import SwiftUI


struct Review: Identifiable {
    
    let id = UUID()
    
    // ID da review na base de dados (chave primária da tabela reviews)
    let id_review: Int
    
    // Texto da mensagem/comentário da review escrita pelo utilizador
    let mensagem: String
    
    // Pontuação numérica da avaliação (tipicamente de 1 a 5 estrelas)
    let avaliacao: Int
    
    // Data e hora quando a review foi criada (formato ISO string do servidor)
    let data_review: String
    
    // Nome do autor que escreveu a review (obtido da tabela utilizadores)
    let autor_nome: String
}


struct ReviewsView: View {
    // MARK: - Estado da Vista (State Variables)
    
    // Array que armazena todas as reviews carregadas do servidor
    // @State permite que o SwiftUI reaja automaticamente às mudanças no array
    @State private var reviews: [Review] = []
    
    // Flag que indica se uma operação de carregamento está em progresso
    // Controla a exibição do ProgressView (indicador de carregamento)
    @State private var isLoading = false
    
    // Flag que controla a exibição do alerta de erro
    // Quando true, o SwiftUI mostra automaticamente o alerta configurado
    @State private var showError = false
    
    // Mensagem de erro personalizada para exibir no alerta
    // Pode variar dependendo do tipo de erro (rede, servidor, etc.)
    @State private var errorMessage = ""
    
    // Flag que controla a exibição da folha modal para adicionar nova review
    // Quando true, o SwiftUI apresenta o AddReviewView como sheet
    @State private var showingAddReview = false
    
    // Flag que garante que as reviews só são carregadas uma vez
    // Evita carregamentos desnecessários quando a vista é reconstruída
    @State private var hasLoadedReviews = false
    
    
    let userId = 1 // Valor exemplo fixo - deve ser dinâmico
    
    
    var body: some View {
        // NavigationView fornece contexto de navegação e barra de título
        NavigationView {
            // Group permite agrupar vistas condicionalmente sem afetar o layout
            Group {
                // Condicional que decide se mostra indicador de carregamento ou conteúdo
                if isLoading {
                    // ProgressView é o indicador de carregamento padrão do SwiftUI
                    // Mostra um spinner circular animado automaticamente
                    ProgressView()
                } else {
                    // ScrollView permite scroll vertical quando o conteúdo excede a tela
                    ScrollView {
                        // LazyVStack carrega vistas apenas quando necessário (otimização de performance)
                        // spacing: 16 adiciona espaço vertical de 16 pontos entre cada item
                        LazyVStack(spacing: 16) {
                            // ForEach itera sobre o array de reviews
                            // Como Review conforma com Identifiable, usa automaticamente review.id
                            ForEach(reviews) { review in
                                // Componente personalizado que renderiza uma review individual
                                ReviewRow(review: review)
                            }
                            
                            // Condicional que mostra mensagem quando não há reviews
                            if reviews.isEmpty {
                                Text("Nenhuma review encontrada")
                                    .font(.headline) // Estilo de fonte para títulos
                                    .foregroundColor(.gray) // Cor cinzenta para indicar estado vazio
                                    .padding() // Adiciona espaço interno ao redor do texto
                            }
                        }
                        .padding() // Adiciona margem externa ao redor de todo o LazyVStack
                    }
                }
            }
            // MARK: - Configuração da Navegação
            
            // Define o título que aparece na barra de navegação
            .navigationTitle("Reviews")
            
            // Configura botões e elementos na barra de navegação
            .toolbar {
                // ToolbarItem permite adicionar items específicos na toolbar
                // placement: .navigationBarTrailing coloca o item no lado direito da barra
                ToolbarItem(placement: .navigationBarTrailing) {
                    // Botão para adicionar nova review
                    Button(action: {
                        // Define showingAddReview como true para apresentar a folha modal
                        showingAddReview = true
                    }) {
                        // Ícone de "plus" do sistema SF Symbols
                        Image(systemName: "plus")
                    }
                }
            }
            
            
            .alert("Erro", isPresented: $showError) {
                // Botão único "OK" com role cancel (estilo padrão de cancelamento)
                Button("OK", role: .cancel) {}
            } message: {
                // Corpo da mensagem do alerta (usa a variável errorMessage)
                Text(errorMessage)
            }
            
            // Folha modal que aparece quando showingAddReview é true
            .sheet(isPresented: $showingAddReview) {
                // Apresenta a vista AddReviewView passando o userId como parâmetro
                AddReviewView(userId: userId)
            }
            
            
            .task {
                // Só carrega reviews se ainda não foram carregadas (evita recarregamentos)
                if !hasLoadedReviews {
                    // Chama função assíncrona para carregar reviews do servidor
                    await loadReviews()
                    // Marca como carregado para evitar futuros carregamentos desnecessários
                    hasLoadedReviews = true
                }
            }
        }
    }
    
   
    private func loadReviews() async {
        // Ativa o indicador de carregamento na UI
        isLoading = true
        
        // Bloco do-catch para tratamento estruturado de erros
        do {
            // Chama APIService.shared.getReviews() que retorna [[String: Any]]
            // Esta é uma operação assíncrona que pode falhar (marked with 'try')
            let results = try await APIService.shared.getReviews()
            
            // MainActor.run garante que as atualizações da UI acontecem na main thread
            // Isso é essencial porque o SwiftUI só pode ser atualizado na thread principal
            await MainActor.run {
                // Mapeia o array de dicionários para array de objetos Review
                reviews = results.map { dict in
                    Review(
                        // Extrai id_review do dicionário, usa 0 como fallback se não existir
                        id_review: dict["id_review"] as? Int ?? 0,
                        // Extrai mensagem do dicionário, usa string vazia como fallback
                        mensagem: dict["mensagem"] as? String ?? "",
                        // Extrai avaliação do dicionário, usa 0 como fallback
                        avaliacao: dict["avaliacao"] as? Int ?? 0,
                        // Extrai data da review do dicionário, usa string vazia como fallback
                        data_review: dict["data_review"] as? String ?? "",
                        // Extrai nome do autor do dicionário, usa string vazia como fallback
                        autor_nome: dict["autor_nome"] as? String ?? ""
                    )
                }
                // Desativa o indicador de carregamento
                isLoading = false
            }
        }
        // Captura erros específicos do servidor (APIError.serverError)
        catch APIError.serverError(let message) {
            await MainActor.run {
                showError = true // Ativa a exibição do alerta de erro
                errorMessage = message // Define a mensagem específica do servidor
                isLoading = false // Desativa o indicador de carregamento
            }
        }
        // Captura erros específicos de rede (APIError.networkError)
        catch APIError.networkError(let message) {
            await MainActor.run {
                showError = true
                // Mensagem user-friendly para problemas de conectividade
                errorMessage = "Erro de conexão. Verifique sua internet e tente novamente."
                isLoading = false
            }
        }
        // Captura qualquer outro erro não especificado
        catch {
            await MainActor.run {
                showError = true
                // Mensagem genérica para erros inesperados
                errorMessage = "Erro ao carregar reviews. Por favor, tente novamente."
                isLoading = false
            }
        }
    }
}


struct ReviewRow: View {
    // Propriedade imutável que contém os dados da review a ser exibida
    let review: Review
    
    
    var body: some View {
        // VStack organiza elementos verticalmente
        // alignment: .leading alinha todo o conteúdo à esquerda
        // spacing: 8 adiciona 8 pontos de espaço entre cada elemento
        VStack(alignment: .leading, spacing: 8) {
            
            
            HStack {
                // Nome do autor da review
                Text(review.autor_nome)
                    .font(.headline) // Estilo de fonte em destaque para o nome
                
                // Spacer empurra as estrelas para a direita (ocupa todo espaço disponível)
                Spacer()
                
                // Container horizontal para as estrelas de avaliação
                HStack {
                    // ForEach cria 5 estrelas (índices de 1 a 5)
                    ForEach(1...5, id: \.self) { index in
                        // Determina se a estrela deve estar preenchida ou vazia
                        // Se index <= review.avaliacao, usa estrela preenchida, senão vazia
                        Image(systemName: index <= review.avaliacao ? "star.fill" : "star")
                            .foregroundColor(.yellow) // Cor amarela para as estrelas
                    }
                }
            }
            
            
            Text(review.mensagem)
                .font(.body) // Estilo de fonte normal para o corpo do texto
            
            // MARK: - Rodapé da Review (Data)
            // Data formatada da review
            Text(formatDate(review.data_review))
                .font(.caption) // Fonte pequena para informação secundária
                .foregroundColor(.gray) // Cor cinzenta para diminuir a importância visual
        }
        .padding(.vertical, 8) // Adiciona 8 pontos de padding vertical (top e bottom)
    }
    
    
    private func formatDate(_ dateString: String) -> String {
        // Cria um DateFormatter para parsing da data ISO
        let formatter = DateFormatter()
        // Define o formato de entrada (ISO 8601 com milissegundos e timezone)
        formatter.dateFormat = "yyyy-MM-dd'T'HH:mm:ss.SSSZ"
        
        // Tenta fazer o parsing da string para um objeto Date
        if let date = formatter.date(from: dateString) {
            // Se successful, reconfigura o formatter para o formato de saída
            formatter.dateFormat = "dd/MM/yyyy"
            // Retorna a data formatada como string
            return formatter.string(from: date)
        }
        
        // Se o parsing falhar, retorna a string original
        return dateString
    }
}


struct AddReviewView: View {
    
    // ID do utilizador que está a criar a review (passado pela vista pai)
    let userId: Int
    
    // Environment value que permite fechar/dismissar a vista modal
    @Environment(\.dismiss) private var dismiss
    
    // MARK: - Estado do Formulário
    
    // Texto da mensagem da review (binding para TextEditor)
    @State private var mensagem = ""
    
    // Avaliação selecionada (1-5 estrelas), inicia com 5 como valor padrão
    @State private var avaliacao = 5
    
    // MARK: - Estado de UI e Erros
    
    // Flag para mostrar alertas de erro
    @State private var showError = false
    
    // Mensagem de erro para exibir no alerta
    @State private var errorMessage = ""
    
    // Flag que indica se a submissão está em progresso
    // Controla a exibição do loading e desabilita controles
    @State private var isLoading = false
    
    
    var body: some View {
        // NavigationView fornece contexto de navegação para a vista modal
        NavigationView {
            // Form cria um formulário com estilo nativo iOS (células agrupadas)
            Form {
                
                // MARK: - Seção de Avaliação (Estrelas)
                // Section cria uma seção agrupada no formulário com cabeçalho
                Section(header: Text("Avaliação")) {
                    // HStack para organizar as 5 estrelas horizontalmente
                    HStack {
                        // ForEach cria 5 estrelas interativas (índices de 1 a 5)
                        ForEach(1...5, id: \.self) { index in
                            // Determina se a estrela está preenchida baseado na avaliação atual
                            Image(systemName: index <= avaliacao ? "star.fill" : "star")
                                .foregroundColor(.yellow) // Cor amarela para as estrelas
                                // onTapGesture permite tocar nas estrelas para selecionar avaliação
                                .onTapGesture {
                                    // Define a avaliação baseada na estrela tocada
                                    avaliacao = index
                                }
                        }
                    }
                    // Desabilita interação com as estrelas durante loading
                    .disabled(isLoading)
                }
                
                // MARK: - Seção de Mensagem (Texto)
                Section(header: Text("Mensagem")) {
                    // TextEditor permite entrada de texto multi-linha
                    TextEditor(text: $mensagem) // Binding para a variável mensagem
                        .frame(height: 100) // Define altura fixa de 100 pontos
                        .disabled(isLoading) // Desabilita durante loading
                }
                
                // MARK: - Seção de Submissão (Botão Enviar)
                Section {
                    // Botão para submeter a review
                    Button(action: {
                        // Task cria um contexto assíncrono para chamar submitReview()
                        Task {
                            await submitReview()
                        }
                    }) {
                        // Condicional que mostra loading ou texto baseado no estado
                        if isLoading {
                            // ProgressView mostra indicador de carregamento circular
                            ProgressView()
                                .progressViewStyle(CircularProgressViewStyle())
                        } else {
                            // Texto normal do botão quando não está carregando
                            Text("Enviar Review")
                        }
                    }
                    // Desabilita botão se mensagem vazia ou durante loading
                    .disabled(mensagem.isEmpty || isLoading)
                }
            }
            // MARK: - Configuração da Navegação Modal
            
            // Define o título da vista modal
            .navigationTitle("Nova Review")
            
            
            .navigationBarItems(trailing: Button("Cancelar") {
                // Quando tocado, fecha a vista modal
                dismiss()
            })
            
            // MARK: - Gestão de Alertas
            
            // Alerta para mostrar erros durante a submissão
            .alert("Erro", isPresented: $showError) {
                // Botão único para fechar o alerta
                Button("OK", role: .cancel) {}
            } message: {
                // Mensagem do erro específico
                Text(errorMessage)
            }
        }
    }
    
    
    private func submitReview() async {
        // MARK: - Validação de Dados
        
        // Valida se a mensagem não está vazia
        guard !mensagem.isEmpty else {
            // Se vazia, mostra erro e retorna sem fazer chamada à API
            showError = true
            errorMessage = "Por favor, escreva uma mensagem"
            return
        }
        
        
        isLoading = true
        
        
        do {
            
            _ = try await APIService.shared.addReview(
                id_utilizador: userId,
                mensagem: mensagem,
                avaliacao: avaliacao,
                id_encomenda: 1 // Este ID deve vir da encomenda específica
            )
            
            await MainActor.run {
                dismiss()
            }
        }
       
        catch APIError.serverError(let message) {
            await MainActor.run {
                showError = true // Ativa alerta de erro
                errorMessage = message // Usa mensagem específica do servidor
                isLoading = false // Desativa loading
            }
        }
        // Captura erros de rede (APIError.networkError)
        catch APIError.networkError(let message) {
            await MainActor.run {
                showError = true
                // Mensagem user-friendly para problemas de conectividade
                errorMessage = "Erro de conexão. Verifique sua internet e tente novamente."
                isLoading = false
            }
        }
        // Captura qualquer outro erro inesperado
        catch {
            await MainActor.run {
                showError = true
                // Mensagem genérica para erros não especificados
                errorMessage = "Erro ao enviar review. Por favor, tente novamente."
                isLoading = false
            }
        }
    }
}


#Preview {
    ReviewsView()
} 