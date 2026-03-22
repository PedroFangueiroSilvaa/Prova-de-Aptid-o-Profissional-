
import SwiftUI


struct HomeView: View {
    
    @EnvironmentObject private var cartManager: CartManager
    
    
    @EnvironmentObject private var authManager: AuthManager
    
    
    @State private var activePromotion = 0
    
    
    @State private var featuredProducts: [[String: Any]] = []
    
   
    @State private var isLoading = false
    
    
    @State private var showError = false
    @State private var errorMessage = ""
    
    
    @State private var navigateToCategory: String? = nil
    
    
    @State private var showRegisterSheet = false
    
    @State private var showLoginSheet = false
    
    let primaryColor = AppTheme.primaryOrange      // Cor principal laranja
    let secondaryColor = Color(red: 0.1, green: 0.1, blue: 0.1)  // Preto intenso
    let accentColor = AppTheme.secondaryOrange     // Laranja secundário
    
   
    let promotions = [
        [
            "title": "NOVA COLEÇÃO",
            "subtitle": "Luvas profissionais com imensa variedade",
            "emoji": "🥊",
            "color": AppTheme.primaryOrange
        ],
        [
            "title": "TREINO INTENSO", 
            "subtitle": "Equipamentos para treinamento avançado",
            "emoji": "🥋",
            "color": AppTheme.darkOrange
        ],
        [
            "title": "PRODUTOS RELÂMPAGO",
            "subtitle": "Os melhores produtos que já viu",
            "emoji": "⚡",
            "color": AppTheme.secondaryOrange
        ]
    ]
    
   
    let categories = [
        ["name": "Luvas", "icon": "hand.raised.fill", "category": "luvas"],
        ["name": "Proteções", "icon": "shield.fill", "category": "protecoes"],
        ["name": "Sacos", "icon": "bag.fill", "category": "sacos"],
        ["name": "Vestuário", "icon": "tshirt.fill", "category": "vestuario"],
        ["name": "Acessórios", "icon": "star.fill", "category": "acessorios"],
    ]
    
    
    var body: some View {
        
        NavigationStack {
            
            ScrollView {
                VStack(spacing: 20) {
                    
                    logoSection
                    
                    promotionalBannerSection
                    
                    categoriesSection
                    
                    featuredProductsSection
                    
                    blogBannerSection
                    
                    footerSection
                    
                    if !authManager.isLoggedIn {
                        VStack(spacing: 10) {
                            Button("Login") {
                                showLoginSheet = true
                            }
                            .padding()
                            .background(AppTheme.primaryOrange)
                            .foregroundColor(.white)
                            .cornerRadius(8)
                            
                            Button("Criar Conta") {
                                showRegisterSheet = true
                            }
                            .padding()
                            .background(AppTheme.secondaryOrange)
                            .foregroundColor(.white)
                            .cornerRadius(8)
                        }
                        .padding()
                    }
                }
            }
            .sheet(isPresented: $showRegisterSheet) {
                RegisterView()
            }
            .sheet(isPresented: $showLoginSheet) {
                LoginView()
            }
           
            .alert("Erro", isPresented: $showError) {
                Button("OK", role: .cancel) {}
            } message: {
                Text(errorMessage)
            }
            
            .onAppear {
                Task {
                    // Carregamento assíncrono de dados do servidor
                    await loadFeaturedProducts()
                }
            }
        }
    }
    
   
    private var logoSection: some View {
        VStack(spacing: 5) {
            // Nome principal da marca
            Text("BOXING FOR LIFE")
                .font(.system(size: 30, weight: .black))    // Fonte grande e bold
                .foregroundColor(primaryColor)              // Cor laranja principal
            
            // Slogan motivacional da marca
            Text("EQUIPA-TE PARA VENCER")
                .font(.subheadline)                         // Fonte menor que o título
                .fontWeight(.medium)                        // Peso médio
                .foregroundColor(secondaryColor)            // Cor preta para contraste
        }
        .padding(.top, 20)                                  // Espaço superior
    }
    
   
    private var promotionalBannerSection: some View {
        // TabView cria navegação horizontal tipo carrossel
        TabView(selection: $activePromotion) {
            // Loop através de todas as promoções disponíveis
            ForEach(0..<promotions.count, id: \.self) { index in
                // Cria view para cada promoção individual
                promotionView(for: index)
                    .padding(.horizontal)                   // Espaço lateral
                    .tag(index)                            // Tag para seleção
            }
        }
        .tabViewStyle(.page(indexDisplayMode: .automatic))  // Nova sintaxe para iOS 18+
        .frame(height: 200)                                           // Altura fixa do banner
    }
    
    
    private func promotionView(for index: Int) -> some View {
        PromotionalBanner(
            title: promotions[index]["title"] as! String,              // Título da promoção
            subtitle: promotions[index]["subtitle"] as! String,        // Subtítulo explicativo
            buttonText: "Ver Mais",                                    // Texto do botão
            backgroundColor: promotions[index]["color"] as! Color,     // Cor de fundo
            action: {
                // TODO: Implementar ação específica para cada promoção
                // Pode navegar para categoria ou produto específico
            },
            imageEmoji: promotions[index]["emoji"] as! String          // Emoji decorativo
        )
    }
    
    
    private var categoriesSection: some View {
        VStack(alignment: .leading, spacing: 15) {
            // Título da secção
            Text("CATEGORIAS")
                .font(.headline)                            // Fonte em destaque
                .fontWeight(.bold)                          // Peso bold
                .padding(.horizontal)                       // Espaço lateral
            
            // Scroll horizontal para as categorias
            ScrollView(.horizontal, showsIndicators: false) {
                HStack(spacing: 15) {
                    // Loop através de todas as categorias
                    ForEach(Array(categories.enumerated()), id: \.offset) { index, category in
                        categoryItem(for: category)
                    }
                }
                .padding(.horizontal)                       // Espaço lateral do scroll
            }
        }
    }
    
   
    private func categoryItem(for category: [String: Any]) -> some View {
        NavigationLink(
            destination: CategoryProductsView(category: category["category"] as! String),
            label: {
                categoryButton(for: category)
            }
        )
    }
    
    
    private func categoryButton(for category: [String: Any]) -> some View {
        VStack {
            // Ícone circular com gradiente laranja
            categoryCircleIcon(icon: category["icon"] as! String)
            
            // Nome da categoria
            Text(category["name"] as! String)
                .font(.caption)                             // Fonte pequena
                .fontWeight(.medium)                        // Peso médio
                .foregroundColor(.black)                    // Cor preta para contraste
        }
        .padding(.vertical, 5)                              // Espaço vertical
    }
    
    
    private func categoryCircleIcon(icon: String) -> some View {
        Circle()
            .fill(LinearGradient(
                gradient: Gradient(colors: [primaryColor, AppTheme.secondaryOrange]),
                startPoint: .topLeading,                    // Gradiente diagonal
                endPoint: .bottomTrailing
            ))
            .frame(width: 70, height: 70)                   // Tamanho fixo
            .overlay(
                Image(systemName: icon)
                    .font(.system(size: 30))                // Ícone grande
                    .foregroundColor(.white)                // Cor branca para contraste
            )
    }
    
   
    private var featuredProductsSection: some View {
        VStack(alignment: .leading, spacing: 15) {
            // Título da secção
            Text("MELHORES AVALIAÇÕES")
                .font(.headline)                            // Fonte em destaque
                .fontWeight(.bold)                          // Peso bold
                .padding(.horizontal)                       // Espaço lateral
            
            // Conteúdo dos produtos (loading ou lista)
            featuredProductsContent
        }
    }
    
    
    private var featuredProductsContent: some View {
        Group {
            if isLoading {
                // Estado de carregamento: mostra spinner com mensagem
                ProgressView("Carregando produtos com melhores avaliações...")
                    .padding()
            } else if featuredProducts.isEmpty {
                // Estado vazio: nenhum produto carregado
                Text("Nenhum produto avaliado disponível")
                    .foregroundColor(.gray)
                    .padding()
            } else {
                // Estado com dados: exibe lista de produtos
                featuredProductsList
            }
        }
    }
    
    
    private var featuredProductsList: some View {
        ScrollView(.horizontal, showsIndicators: false) {
            HStack(spacing: 15) {
                // Loop através de todos os produtos carregados
                ForEach(0..<featuredProducts.count, id: \.self) { index in
                    productLink(for: featuredProducts[index], index: index)
                }
            }
            .padding(.horizontal)                           // Espaço lateral
        }
    }
    
    
    private func productLink(for product: [String: Any], index: Int) -> some View {
        NavigationLink(
            destination: ProductDetailView(product: product),
            label: {
                productCard(for: product)
            }
        )
    }
    
    
    private func productCard(for product: [String: Any]) -> some View {
        // Processamento robusto do preço para lidar com diferentes formatos
        let price: Double
        if let priceDouble = product["preco"] as? Double {
            // Preço já é Double
            price = priceDouble
        } else if let priceString = product["preco"] as? String, 
                  let parsedPrice = Double(priceString) {
            // Preço é String mas conversível para Double
            price = parsedPrice
        } else {
            // Fallback para preço inválido
            price = 0.0
            print("⚠️ Erro ao processar preço do produto: \(product["nome"] as? String ?? "desconhecido")")
        }
        
        return FeaturedProductCard(
            name: product["nome"] as? String ?? "Produto",              // Nome do produto
            price: price,                                               // Preço processado
            imageUrl: APIService.shared.processImageURL(product["imagem"] as? String ?? ""), // URL da imagem
            description: product["categoria_nome"] as? String ?? "Sem categoria",           // Categoria
            onAddToCart: {
                // TODO: Implementar lógica de adição ao carrinho
                // Deve integrar com CartManager
            },
            productId: product["id"] as? Int ?? 0,                      // ID do produto
            codigoBase: product["codigo_base"] as? String               // Código base para reviews
        )
    }
    
    
    private var blogBannerSection: some View {
        VStack(spacing: 15) {
            HStack {
                // Informação do blog (lado esquerdo)
                VStack(alignment: .leading, spacing: 2) {
                    Text("BLOG DE BOXE")
                        .font(.headline)                    // Fonte em destaque
                        .fontWeight(.bold)                  // Peso bold
                    Text("Dicas de treino e nutrição")
                        .font(.subheadline)                 // Fonte menor
                        .opacity(0.8)                       // Transparência para subtítulo
                }
                
                Spacer()                                    // Empurra botão para direita
                
                // Botão de navegação (lado direito)
                NavigationLink(destination: BlogView()) {
                    Text("Ver Blog")
                        .font(.caption)                     // Fonte pequena
                        .fontWeight(.bold)                  // Peso bold
                        .padding(.horizontal, 10)           // Espaço horizontal interno
                        .padding(.vertical, 5)              // Espaço vertical interno
                        .background(AppTheme.secondaryOrange) // Cor de fundo laranja
                        .foregroundColor(.white)            // Texto branco
                        .cornerRadius(15)                   // Bordas arredondadas
                }
            }
            .padding()                                      // Espaço interno
            .background(Color.gray.opacity(0.1))            // Fundo cinzento claro
            .cornerRadius(12)                               // Bordas arredondadas
            .padding(.horizontal)                           // Espaço lateral
        }
    }
    
    
    
    private var footerSection: some View {
        VStack(spacing: 15) {
            Text("BOXING FOR LIFE")
                .font(.headline)
                .fontWeight(.black)
                .foregroundColor(primaryColor)
            
            Text("A sua loja especializada em artigos de boxe de alta qualidade. Equipamentos profissionais para atletas de todos os níveis.")
                .font(.caption)
                .multilineTextAlignment(.center)
                .padding(.horizontal, 30)
                .foregroundColor(.secondary)
            
            // Removido o ícone de Lojas, mantendo apenas Email e Contacto
            HStack(spacing: 20) {
                socialMediaButton(icon: "envelope.fill", text: "Email")
                socialMediaButton(icon: "phone.fill", text: "Contacto")
            }
            .padding(.top, 5)
        }
        .padding(.vertical, 25)
        .padding(.horizontal)
        .background(Color.gray.opacity(0.05))
        .cornerRadius(12)
        .padding(.horizontal)
    }
    
    
    private func loadFeaturedProducts() async {
        isLoading = true
        
        do {
            let allProducts = try await APIService.shared.getProducts()
            
            if allProducts.count > 0 {
                // Criar array para armazenar produtos com suas avaliações
                var productsWithRatings: [(product: [String: Any], averageRating: Double, reviewCount: Int)] = []
                
                // Para cada produto, buscar suas avaliações
                for product in allProducts {
                    if let codigoBase = product["codigo_base"] as? String {
                        do {
                            let reviews = try await APIService.shared.fetchProductReviews(codigoBase: codigoBase)
                            
                            let averageRating: Double
                            if !reviews.isEmpty {
                                let total = reviews.map { Double($0.classificacao) }.reduce(0, +)
                                averageRating = total / Double(reviews.count)
                            } else {
                                averageRating = 0.0
                            }
                            
                            productsWithRatings.append((
                                product: product,
                                averageRating: averageRating,
                                reviewCount: reviews.count
                            ))
                        } catch {
                            // Se falhar ao buscar reviews, adiciona com rating 0
                            productsWithRatings.append((
                                product: product,
                                averageRating: 0.0,
                                reviewCount: 0
                            ))
                        }
                    }
                }
                
                // Ordenar por melhor avaliação (rating + número de avaliações como critério de desempate)
                let sortedProducts = productsWithRatings.sorted { first, second in
                    // Prioridade 1: Produtos com rating mais alto
                    if first.averageRating != second.averageRating {
                        return first.averageRating > second.averageRating
                    }
                    // Prioridade 2: Em caso de empate, produtos com mais avaliações
                    return first.reviewCount > second.reviewCount
                }
                
                // Selecionar os top 5 produtos com melhores avaliações
                let topProducts = sortedProducts.prefix(5).map { $0.product }
                
                await MainActor.run {
                    self.featuredProducts = Array(topProducts)
                    self.isLoading = false
                }
            } else {
                await MainActor.run {
                    self.featuredProducts = []
                    self.isLoading = false
                }
            }
        } catch {
            await MainActor.run {
                self.errorMessage = "Erro ao carregar produtos: \(error.localizedDescription)"
                self.showError = true
                self.isLoading = false
            }
        }
    }
    
    // Botão para redes sociais atualizado com tema laranja
    private func socialMediaButton(icon: String, text: String) -> some View {
        VStack(spacing: 5) {
            Image(systemName: icon)
                .font(.system(size: 18))
                .foregroundColor(primaryColor)
            
            Text(text)
                .font(.caption2)
                .foregroundColor(.primary)
        }
        .frame(minWidth: 60)
        .padding(.vertical, 8)
        .background(Color.white)
        .cornerRadius(8)
        .shadow(color: Color.black.opacity(0.05), radius: 5, x: 0, y: 2)
    }
}


struct CategoryProductsView: View {
    let category: String
    @State private var products: [[String: Any]] = []
    @State private var isLoading = true
    @State private var showError = false
    @State private var errorMessage = ""
    
    var body: some View {
        Group {
            if isLoading {
                ProgressView("Carregando produtos...")
            } else if products.isEmpty {
                emptyProductsView
            } else {
                productsListView
            }
        }
        .navigationTitle(categoryDisplayName())
        .alert("Erro", isPresented: $showError) {
            Button("OK", role: .cancel) {}
        } message: {
            Text(errorMessage)
        }
        .onAppear {
            Task {
                await loadCategoryProducts()
            }
        }
    }
    
    private var emptyProductsView: some View {
        VStack {
            Image(systemName: "bag.badge.questionmark")
                .font(.system(size: 50))
                .padding()
            
            Text("Nenhum produto encontrado nesta categoria")
                .font(.headline)
            
            Button("Tentar novamente") {
                Task {
                    await loadCategoryProducts()
                }
            }
            .padding()
        }
    }
    
    
    
    private var productsListView: some View {
        ScrollView {
            LazyVStack(spacing: 16) {
                ForEach(0..<products.count, id: \.self) { index in
                    productLink(at: index)
                }
            }
            .padding()
        }
    }
    
    
    private func productLink(at index: Int) -> some View {
        let product = products[index]
        return NavigationLink(
            destination: ProductDetailView(product: product),
            label: {
                ProductRow(product: product)
            }
        )
        .buttonStyle(PlainButtonStyle())
    }
    
    
    private func categoryDisplayName() -> String {
        switch category {
        case "luvas": return "Luvas"
        case "protecoes": return "Proteções"
        case "sacos": return "Sacos"
        case "vestuario": return "Vestuário"
        case "acessorios": return "Acessórios"
        default: return "Produtos"
        }
    }
    
    
    private func loadCategoryProducts() async {
        isLoading = true
        
        do {
            // Carrega todos os produtos primeiro
            let allProducts = try await APIService.shared.getProducts()
            
            // Filtra produtos da categoria desejada
            let filteredProducts = allProducts.filter { product in
                if let categoryName = product["categoria_nome"] as? String {
                    // Comparação insensível a maiúsculas/minúsculas
                    let normalizedCategory = categoryName.lowercased()
                    let targetCategory = category.lowercased()
                    
                    // Verifica se categoria do produto contém categoria alvo
                    return normalizedCategory.contains(targetCategory)
                }
                return false
            }
            
            // Atualiza UI na main thread
            await MainActor.run {
                self.products = filteredProducts
                self.isLoading = false
            }
        } catch {
            // Tratamento de erro
            await MainActor.run {
                self.errorMessage = "Erro ao carregar produtos: \(error.localizedDescription)"
                self.showError = true
                self.isLoading = false
            }
        }
    }
}

#Preview {
    HomeView()
        .environmentObject(CartManager())
        .environmentObject(AuthManager.shared)
}
