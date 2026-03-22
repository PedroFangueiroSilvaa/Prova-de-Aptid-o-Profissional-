

import SwiftUI

// Estrutura que representa um produto básico na lista
struct Product: Identifiable {
    let id = UUID()                // Identificador único para SwiftUI
    let codigo_base: String        // Código base do produto
    let nome: String              // Nome do produto
    let preco: Double             // Preço do produto
    let imagem: String            // URL ou nome da imagem
    let marca_nome: String        // Nome da marca
    let categoria_nome: String    // Nome da categoria
}

// Estrutura que representa detalhes específicos de um produto
struct ProductDetail: Identifiable {
    let id = UUID()               // Identificador único
    let imagem: String           // URL da imagem
    let nome: String             // Nome do produto
    let marca_nome: String       // Nome da marca
    let categoria_nome: String   // Nome da categoria
    let preco: Double            // Preço
    let tamanho: String          // Tamanho disponível
    let cor: String              // Cor disponível
    let stock: Int               // Quantidade em stock
}

// Estrutura principal que representa a tela de produtos
struct ProductsView: View {
    // Lista de produtos carregados do servidor
    @State private var products: [[String: Any]] = []
    @State private var filteredProducts: [[String: Any]] = []
    
    // Estados para gestão de carregamento e erros
    @State private var isLoading = false
    @State private var showError = false
    @State private var errorMessage = ""
    
    // Estados para gestão de seleção de produtos
    @State private var selectedProduct: [String: Any]?
    @State private var showProductDetail = false
    
    // Estados para filtros
    @State private var searchText = ""
    @State private var selectedBrand: String? = nil
    @State private var selectedCategory: String? = nil
    @State private var showFilters = false
    @State private var availableBrands: [String] = []
    @State private var availableCategories: [String] = []
    
    // Flag para evitar carregar produtos múltiplas vezes
    @State private var hasLoadedProducts = false
    
    // Constrói a interface visual da tela de produtos
    var body: some View {
        // NavigationView permite navegação e título
        NavigationView {
            VStack(spacing: 0) {
                // Barra de pesquisa e filtros
                searchAndFiltersSection
                
                // Separador visual
                Divider()
                
                // Conteúdo principal
                Group {
                    // Se está carregando, mostra indicador de progresso
                    if isLoading {
                        ProgressView("Carregando produtos...")
                            .frame(maxWidth: .infinity, maxHeight: .infinity)
                    } 
                    // Se não há produtos filtrados, mostra tela de estado vazio
                    else if filteredProducts.isEmpty {
                        emptyStateView
                    } 
                    // Se há produtos filtrados, mostra a lista
                    else {
                        productsListView
                    }
                }
            }
            .navigationTitle("Produtos")
            // Alert para mostrar erros
            .alert("Erro", isPresented: $showError) {
                Button("OK", role: .cancel) {}
            } message: {
                Text(errorMessage)
            }
            // Modal que mostra detalhes do produto selecionado
            .sheet(isPresented: $showProductDetail) {
                if let product = selectedProduct {
                    ProductDetailView(product: product)
                }
            }
            // Carrega produtos quando a tela aparece pela primeira vez
            .task {
                if !hasLoadedProducts {
                    await loadProducts()
                    hasLoadedProducts = true
                }
            }
            // Aplica filtros quando critérios mudam
            .onChange(of: searchText) { _ in applyFilters() }
            .onChange(of: selectedBrand) { _ in applyFilters() }
            .onChange(of: selectedCategory) { _ in applyFilters() }
        }
    }
    
    // MARK: - UI Components
    
    // Secção de pesquisa e filtros
    private var searchAndFiltersSection: some View {
        VStack(spacing: 12) {
            // Barra de pesquisa
            HStack {
                Image(systemName: "magnifyingglass")
                    .foregroundColor(.gray)
                
                TextField("Pesquisar produtos...", text: $searchText)
                    .textFieldStyle(RoundedBorderTextFieldStyle())
                
                // Botão de filtros
                Button(action: {
                    showFilters.toggle()
                }) {
                    Image(systemName: showFilters ? "line.3.horizontal.decrease.circle.fill" : "line.3.horizontal.decrease.circle")
                        .font(.title2)
                        .foregroundColor(AppTheme.primaryOrange)
                }
            }
            .padding(.horizontal)
            
            // Chips de filtros ativos
            if selectedBrand != nil || selectedCategory != nil {
                activeFiltersView
            }
            
            // Painel de filtros (expansível)
            if showFilters {
                filtersPanel
            }
        }
        .padding(.vertical, 8)
        .background(Color(.systemGray6))
    }
    
    // Chips dos filtros ativos
    private var activeFiltersView: some View {
        ScrollView(.horizontal, showsIndicators: false) {
            HStack(spacing: 8) {
                if let brand = selectedBrand {
                    FilterChip(
                        title: "Marca: \(brand)",
                        onRemove: { selectedBrand = nil }
                    )
                }
                
                if let category = selectedCategory {
                    FilterChip(
                        title: "Categoria: \(category)",
                        onRemove: { selectedCategory = nil }
                    )
                }
                
                // Botão limpar todos os filtros
                if selectedBrand != nil || selectedCategory != nil {
                    Button("Limpar Tudo") {
                        clearAllFilters()
                    }
                    .font(.caption)
                    .padding(.horizontal, 12)
                    .padding(.vertical, 6)
                    .background(Color.red.opacity(0.1))
                    .foregroundColor(.red)
                    .cornerRadius(16)
                }
            }
            .padding(.horizontal)
        }
    }
    
    // Painel de filtros
    private var filtersPanel: some View {
        VStack(alignment: .leading, spacing: 16) {
            // Filtro por marca
            VStack(alignment: .leading, spacing: 8) {
                Text("Marcas")
                    .font(.headline)
                    .foregroundColor(AppTheme.text)
                
                ScrollView(.horizontal, showsIndicators: false) {
                    HStack(spacing: 8) {
                        ForEach(availableBrands, id: \.self) { brand in
                            FilterOptionButton(
                                title: brand,
                                isSelected: selectedBrand == brand,
                                onTap: {
                                    selectedBrand = selectedBrand == brand ? nil : brand
                                }
                            )
                        }
                    }
                    .padding(.horizontal)
                }
            }
            
            // Filtro por categoria
            VStack(alignment: .leading, spacing: 8) {
                Text("Categorias")
                    .font(.headline)
                    .foregroundColor(AppTheme.text)
                
                ScrollView(.horizontal, showsIndicators: false) {
                    HStack(spacing: 8) {
                        ForEach(availableCategories, id: \.self) { category in
                            FilterOptionButton(
                                title: category,
                                isSelected: selectedCategory == category,
                                onTap: {
                                    selectedCategory = selectedCategory == category ? nil : category
                                }
                            )
                        }
                    }
                    .padding(.horizontal)
                }
            }
        }
        .padding()
        .background(Color(.systemBackground))
        .cornerRadius(12)
        .padding(.horizontal)
    }
    
    // Vista de estado vazio
    private var emptyStateView: some View {
        VStack(spacing: 20) {
            // Ícone apropriado baseado no estado
            Image(systemName: hasActiveFilters ? "line.3.horizontal.decrease" : "bag")
                .font(.system(size: 60))
                .foregroundColor(.gray)
            
            // Mensagem baseada no estado
            Text(hasActiveFilters ? "Nenhum produto encontrado com os filtros aplicados" : "Nenhum produto encontrado")
                .font(.title2)
                .foregroundColor(.gray)
                .multilineTextAlignment(.center)
            
            // Botões de ação
            VStack(spacing: 12) {
                if hasActiveFilters {
                    Button("Limpar Filtros") {
                        clearAllFilters()
                    }
                    .padding()
                    .background(AppTheme.primaryOrange)
                    .foregroundColor(.white)
                    .cornerRadius(8)
                }
                
                Button("Tentar novamente") {
                    Task {
                        await loadProducts()
                    }
                }
                .padding()
                .background(Color.blue)
                .foregroundColor(.white)
                .cornerRadius(8)
            }
        }
        .padding()
    }
    
    // Lista de produtos
    private var productsListView: some View {
        ScrollView {
            // LazyVStack só carrega elementos visíveis (performance)
            LazyVStack(spacing: 16) {
                // Itera sobre todos os produtos filtrados
                ForEach(0..<filteredProducts.count, id: \.self) { index in
                    let product = filteredProducts[index]
                    
                    // Componente que mostra um produto individual
                    ProductRow(product: product)
                        // Ação quando utilizador toca no produto
                        .onTapGesture {
                            print("Produto selecionado: \(product["nome"] as? String ?? "")")
                            print("Código base: \(product["codigo_base"] as? String ?? "não disponível")")
                            selectedProduct = product
                            showProductDetail = true
                        }
                }
            }
            .padding()
        }
    }
    
    // MARK: - Helper Properties
    
    // Verifica se há filtros ativos
    private var hasActiveFilters: Bool {
        !searchText.isEmpty || selectedBrand != nil || selectedCategory != nil
    }
    
    // MARK: - Functions
    
    // Função que carrega produtos do servidor
    private func loadProducts() async {
        print("Iniciando carregamento de produtos...")
        isLoading = true
        
        do {
            let fetchedProducts = try await APIService.shared.getProducts()
            print("Produtos recebidos do servidor: \(fetchedProducts.count)")
            
            // Processar os produtos
            var processedProducts = fetchedProducts
            for i in 0..<processedProducts.count {
                if let preco = processedProducts[i]["preco"] as? Double {
                    processedProducts[i]["preco"] = preco
                } else if let precoString = processedProducts[i]["preco"] as? String,
                          let preco = Double(precoString) {
                    processedProducts[i]["preco"] = preco
                } else {
                    print("Erro ao processar preço do produto \(i)")
                    processedProducts[i]["preco"] = 0.0
                }
                
                // Verificar se o código base está presente
                if let codigo = processedProducts[i]["codigo_base"] as? String {
                    print("Produto \(i) - Código base: \(codigo)")
                } else {
                    print("Produto \(i) - Código base não encontrado")
                }
            }
            
            await MainActor.run {
                products = processedProducts
                print("Produtos processados com sucesso: \(products.count)")
                isLoading = false
                
                // Atualiza filtros após carregar produtos
                updateAvailableFilters()
                applyFilters()
            }
        } catch APIError.connectionError(let message) {
            print("Erro de conexão: \(message)")
            await MainActor.run {
                errorMessage = message
                showError = true
                isLoading = false
            }
        } catch APIError.serverError(let message) {
            print("Erro do servidor: \(message)")
            await MainActor.run {
                errorMessage = "Erro do servidor: \(message)"
                showError = true
                isLoading = false
            }
        } catch {
            print("Erro inesperado: \(error)")
            await MainActor.run {
                errorMessage = "Erro ao carregar produtos: \(error.localizedDescription)"
                showError = true
                isLoading = false
            }
        }
    }
    
    // Atualiza listas de marcas e categorias disponíveis
    private func updateAvailableFilters() {
        let brands = Set(products.compactMap { $0["marca_nome"] as? String })
        let categories = Set(products.compactMap { $0["categoria_nome"] as? String })
        
        availableBrands = Array(brands).sorted()
        availableCategories = Array(categories).sorted()
    }
    
    // Aplica filtros aos produtos
    private func applyFilters() {
        var filtered = products
        
        // Filtro por texto de pesquisa
        if !searchText.isEmpty {
            filtered = filtered.filter { product in
                let name = product["nome"] as? String ?? ""
                let brand = product["marca_nome"] as? String ?? ""
                let category = product["categoria_nome"] as? String ?? ""
                
                return name.localizedCaseInsensitiveContains(searchText) ||
                       brand.localizedCaseInsensitiveContains(searchText) ||
                       category.localizedCaseInsensitiveContains(searchText)
            }
        }
        
        // Filtro por marca
        if let selectedBrand = selectedBrand {
            filtered = filtered.filter { product in
                (product["marca_nome"] as? String) == selectedBrand
            }
        }
        
        // Filtro por categoria
        if let selectedCategory = selectedCategory {
            filtered = filtered.filter { product in
                (product["categoria_nome"] as? String) == selectedCategory
            }
        }
        
        filteredProducts = filtered
        print("Filtros aplicados: \(filteredProducts.count) produtos de \(products.count) total")
    }
    
    // Limpa todos os filtros
    private func clearAllFilters() {
        searchText = ""
        selectedBrand = nil
        selectedCategory = nil
    }
}

// MARK: - Filter Components

// Componente para chips de filtros ativos
struct FilterChip: View {
    let title: String
    let onRemove: () -> Void
    
    var body: some View {
        HStack(spacing: 4) {
            Text(title)
                .font(.caption)
                .lineLimit(1)
            
            Button(action: onRemove) {
                Image(systemName: "xmark.circle.fill")
                    .font(.caption)
            }
        }
        .padding(.horizontal, 12)
        .padding(.vertical, 6)
        .background(AppTheme.primaryOrange.opacity(0.1))
        .foregroundColor(AppTheme.primaryOrange)
        .cornerRadius(16)
    }
}

// Componente para botões de opções de filtro
struct FilterOptionButton: View {
    let title: String
    let isSelected: Bool
    let onTap: () -> Void
    
    var body: some View {
        Button(action: onTap) {
            Text(title)
                .font(.subheadline)
                .padding(.horizontal, 16)
                .padding(.vertical, 8)
                .background(isSelected ? AppTheme.primaryOrange : Color.gray.opacity(0.2))
                .foregroundColor(isSelected ? .white : .primary)
                .cornerRadius(20)
        }
        .buttonStyle(PlainButtonStyle())
    }
}

struct ProductRow: View {
    let product: [String: Any]
    @ObservedObject private var favoritesManager = FavoritesManager.shared
    @State private var imageLoadError = false
    @State private var imageLoadAttempts = 0
    @State private var isUpdatingFavorite = false
    private let maxLoadAttempts = 3
    
    var body: some View {
        HStack(spacing: 16) {
            if let imageUrlString = product["imagem"] as? String,
               let imageUrl = URL(string: APIService.shared.processImageURL(imageUrlString)) {
                AsyncImage(url: imageUrl) { phase in
                    switch phase {
                    case .empty:
                        Color.gray
                    case .success(let image):
                        image
                            .resizable()
                            .aspectRatio(contentMode: .fill)
                    case .failure(let error):
                        Color.gray
                            .onAppear {
                                print("Erro ao carregar imagem do produto: \(error.localizedDescription)")
                                print("URL da imagem: \(imageUrl)")
                                print("Tentativa \(imageLoadAttempts + 1) de \(maxLoadAttempts)")
                                
                                if imageLoadAttempts < maxLoadAttempts {
                                    imageLoadAttempts += 1
                                    // Tentar recarregar a imagem após um breve delay
                                    DispatchQueue.main.asyncAfter(deadline: .now() + 1) {
                                        // Forçar uma nova tentativa de carregamento
                                        imageLoadError = true
                                        imageLoadError = false
                                    }
                                }
                            }
                    @unknown default:
                        Color.gray
                    }
                }
                .frame(width: 80, height: 80)
                .cornerRadius(8)
            } else {
                Color.gray
                    .frame(width: 80, height: 80)
                    .cornerRadius(8)
                    .onAppear {
                        print("URL da imagem inválida para o produto: \(product["nome"] as? String ?? "desconhecido")")
                    }
            }
            
            VStack(alignment: .leading, spacing: 4) {
                Text(product["nome"] as? String ?? "Nome não disponível")
                    .font(.headline)
                    .lineLimit(1)
                Text(product["marca_nome"] as? String ?? "Marca não disponível")
                    .font(.subheadline)
                    .foregroundColor(.gray)
                    .lineLimit(1)
                
                // Rating do produto
                if let codigoBase = product["codigo_base"] as? String {
                    ProductRatingView(codigoBase: codigoBase)
                }
                
                if let preco = product["preco"] as? Double {
                    Text("€\(String(format: "%.2f", preco))")
                        .font(.system(.body, design: .rounded))
                        .foregroundColor(.blue)
                }
                if let codigo = product["codigo_base"] as? String {
                    Text("Código: \(codigo)")
                        .font(.caption)
                        .foregroundColor(.gray)
                        .lineLimit(1)
                }
            }
            .frame(height: 80)
            Spacer()
            
            // Botão de favorito com feedback visual
            if let codigo = product["codigo_base"] as? String {
                Button(action: {
                    print("🔵 [ProductRow] Botão de favorito clicado para produto: \(codigo)")
                    isUpdatingFavorite = true
                    let isFavorite = favoritesManager.isFavorite(codigoBase: codigo)
                    
                    if isFavorite {
                        print("🔵 [ProductRow] Removendo favorito...")
                        favoritesManager.removeFavorite(codigoBase: codigo)
                    } else {
                        print("🔵 [ProductRow] Adicionando favorito...")
                        favoritesManager.addFavorite(codigoBase: codigo)
                    }
                    
                    // Reset do estado após um breve delay
                    DispatchQueue.main.asyncAfter(deadline: .now() + 0.5) {
                        isUpdatingFavorite = false
                    }
                }) {
                    ZStack {
                        if isUpdatingFavorite {
                            ProgressView()
                                .scaleEffect(0.7)
                        } else {
                            Image(systemName: favoritesManager.isFavorite(codigoBase: codigo) ? "heart.fill" : "heart")
                                .foregroundColor(.red)
                                .imageScale(.large)
                        }
                    }
                    .frame(width: 24, height: 24)
                    .padding(8)
                }
                .buttonStyle(PlainButtonStyle())
                .disabled(isUpdatingFavorite)
            }
        }
        .padding()
        .frame(height: 120) // Altura fixa para todos os retângulos de produtos
        .background(Color(.systemBackground))
        .cornerRadius(12)
        .shadow(radius: 2)
        .opacity(isUpdatingFavorite ? 0.7 : 1.0)
    }
}

struct ProductDetailView: View {
    let product: [String: Any]
    @EnvironmentObject var cartManager: CartManager
    @State private var details: [[String: Any]] = []
    @State private var isLoading = false
    @State private var showError = false
    @State private var errorMessage = ""
    @State private var selectedSize: String?
    @State private var selectedColor: String?
    @State private var quantity = 1
    @State private var showAddToCartAlert = false
    @State private var hasLoadedDetails = false
    
    var body: some View {
        ScrollView {
            VStack(alignment: .leading, spacing: 16) {
                if isLoading {
                    ProgressView("Carregando detalhes...")
                        .frame(maxWidth: .infinity, maxHeight: .infinity)
                } else {
                    productImage
                    productInfo
                    if !details.isEmpty {
                        productOptions
                    } else {
                        Text("Nenhum detalhe disponível para este produto")
                            .foregroundColor(.gray)
                            .padding()
                    }
                    
                    // Seção de reviews
                    if let codigoBase = product["codigo_base"] as? String {
                        Divider()
                            .padding(.vertical)
                        
                        ProductReviewsView(codigoBase: codigoBase)
                    }
                }
            }
        }
        .navigationBarTitleDisplayMode(.inline)
        .alert("Erro", isPresented: $showError) {
            Button("OK", role: .cancel) {}
        } message: {
            Text(errorMessage)
        }
        .alert("Sucesso", isPresented: $showAddToCartAlert) {
            Button("OK", role: .cancel) {}
        } message: {
            Text("Produto adicionado ao carrinho com sucesso!")
        }
        .task {
            if !hasLoadedDetails {
                await loadProductDetails()
                hasLoadedDetails = true
            }
        }
    }
    
    private var productImage: some View {
        Group {
            if let imageUrlString = product["imagem"] as? String,
               let imageUrl = URL(string: APIService.shared.processImageURL(imageUrlString)) {
                AsyncImage(url: imageUrl) { phase in
                    switch phase {
                    case .empty:
                        Color.gray
                    case .success(let image):
                        image
                            .resizable()
                            .aspectRatio(contentMode: .fit)
                    case .failure(let error):
                        Color.gray
                            .onAppear {
                                print("Erro ao carregar imagem do produto: \(error.localizedDescription)")
                                print("URL da imagem: \(imageUrl)")
                            }
                    @unknown default:
                        Color.gray
                    }
                }
            } else {
                Color.gray
                    .onAppear {
                        print("URL da imagem inválida para o produto: \(product["nome"] as? String ?? "desconhecido")")
                    }
            }
        }
        .frame(height: 300)
    }
    
    private var productInfo: some View {
        // Processando corretamente o preço para evitar valor zero
        let price: Double
        if let priceDouble = product["preco"] as? Double {
            price = priceDouble
        } else if let priceString = product["preco"] as? String, 
                  let parsedPrice = Double(priceString) {
            price = parsedPrice
        } else {
            price = 0.0
            print("⚠️ Erro ao processar preço do produto na tela de detalhes: \(product["nome"] as? String ?? "desconhecido")")
        }
        
        return VStack(alignment: .leading, spacing: 8) {
            Text(product["nome"] as? String ?? "")
                .font(.title)
                .bold()
            
            Text(product["marca_nome"] as? String ?? "")
                .font(.headline)
                .foregroundColor(.gray)
            
            Text("€\(String(format: "%.2f", price))")
                .font(.system(.title2, design: .rounded))
                .foregroundColor(.blue)
        }
        .padding(.horizontal)
    }
    
    private var productOptions: some View {
        VStack(alignment: .leading, spacing: 16) {
            Text("Tamanhos Disponíveis")
                .font(.headline)
            
            ScrollView(.horizontal, showsIndicators: false) {
                HStack(spacing: 8) {
                    let uniqueSizes = Array(Set(details.compactMap { $0["tamanho"] as? String })).sorted()
                    ForEach(uniqueSizes, id: \.self) { size in
                        SizeButton(size: size, isSelected: selectedSize == size) {
                            selectedSize = size
                        }
                    }
                }
            }
            
            Text("Cores Disponíveis")
                .font(.headline)
            
            ScrollView(.horizontal, showsIndicators: false) {
                HStack(spacing: 8) {
                    let uniqueColors = Array(Set(details.compactMap { $0["cor"] as? String })).sorted()
                    ForEach(uniqueColors, id: \.self) { color in
                        ColorButton(color: color, isSelected: selectedColor == color) {
                            selectedColor = color
                        }
                    }
                }
            }
            
            HStack {
                Text("Quantidade:")
                    .font(.headline)
                
                Stepper("\(quantity)", value: $quantity, in: 1...10)
            }
            
            Button(action: {
                Task {
                    await addToCart()
                }
            }) {
                Text("Adicionar ao Carrinho")
                    .font(.headline)
                    .foregroundColor(.white)
                    .frame(maxWidth: .infinity)
                    .padding()
                    .background(Color.blue)
                    .cornerRadius(10)
            }
            .disabled(selectedSize == nil || selectedColor == nil)
        }
        .padding()
    }
    
    private func loadProductDetails() async {
        guard let codigo_base = product["codigo_base"] as? String else {
            print("Erro: código_base não encontrado no produto")
            errorMessage = "Erro: código do produto inválido"
            showError = true
            return
        }
        
        print("Carregando detalhes para o produto com código: \(codigo_base)")
        isLoading = true
        
        do {
            details = try await APIService.shared.getProductDetails(codigo_base: codigo_base)
            print("Detalhes carregados com sucesso: \(details.count) variações")
            
            if details.isEmpty {
                print("Nenhuma variação encontrada para o produto")
                errorMessage = "Não foram encontradas variações para este produto"
                showError = true
            }
        } catch APIError.serverError(let message) {
            print("Erro do servidor: \(message)")
            errorMessage = message
            showError = true
        } catch APIError.networkError(let message) {
            print("Erro de rede: \(message)")
            errorMessage = "Erro de conexão. Verifique sua internet e tente novamente."
            showError = true
        } catch {
            print("Erro inesperado ao carregar detalhes: \(error)")
            errorMessage = "Erro ao carregar detalhes do produto. Por favor, tente novamente."
            showError = true
        }
        
        isLoading = false
    }
    
    private func addToCart() async {
        guard let selectedSize = selectedSize,
              let selectedColor = selectedColor,
              let codigo_base = product["codigo_base"] as? String else {
            print("Dados incompletos para adicionar ao carrinho")
            errorMessage = "Dados incompletos para adicionar ao carrinho"
            showError = true
            return
        }
        
        // Encontrar o SKU correto baseado no tamanho e cor selecionados
        let sku = details.first { detail in
            detail["tamanho"] as? String == selectedSize &&
            detail["cor"] as? String == selectedColor
        }?["sku"] as? String
        
        guard let validSKU = sku else {
            print("SKU não encontrado para a combinação selecionada")
            errorMessage = "Erro ao gerar SKU do produto"
            showError = true
            return
        }
        
        print("Adicionando ao carrinho - SKU: \(validSKU), Quantidade: \(quantity)")
        
        do {
            let result = try await cartManager.addToCart(
                sku: validSKU,
                quantidade: quantity,
                preco_unitario: product["preco"] as? Double ?? 0.0,
                nome: product["nome"] as? String ?? "",
                imagem: product["imagem"] as? String ?? "",
                tamanho: selectedSize,
                cor: selectedColor
            )
            
            print("Resposta do servidor: \(result)")
            showAddToCartAlert = true
        } catch APIError.connectionError(let message) {
            print("Erro de conexão: \(message)")
            errorMessage = message
            showError = true
        } catch APIError.serverError(let message) {
            print("Erro do servidor: \(message)")
            errorMessage = "Erro do servidor: \(message)"
            showError = true
        } catch {
            print("Erro inesperado: \(error)")
            errorMessage = "Erro ao adicionar ao carrinho: \(error.localizedDescription)"
            showError = true
        }
    }
}

struct SizeButton: View {
    let size: String
    let isSelected: Bool
    let action: () -> Void
    
    var body: some View {
        Button(action: action) {
            Text(size)
                .font(.subheadline)
                .padding(.horizontal, 16)
                .padding(.vertical, 8)
                .background(isSelected ? Color.blue : Color.gray.opacity(0.2))
                .foregroundColor(isSelected ? .white : .primary)
                .cornerRadius(8)
        }
    }
}

struct ColorButton: View {
    let color: String
    let isSelected: Bool
    let action: () -> Void
    
    var body: some View {
        Button(action: action) {
            Text(color)
                .font(.subheadline)
                .padding(.horizontal, 16)
                .padding(.vertical, 8)
                .background(isSelected ? Color.blue : Color.gray.opacity(0.2))
                .foregroundColor(isSelected ? .white : .primary)
                .cornerRadius(8)
        }
    }
}

struct AddToCartView: View {
    let product: Product
    @Environment(\.dismiss) private var dismiss
    @StateObject private var cartManager = CartManager()
    @State private var selectedQuantity = 1
    @State private var showError = false
    @State private var errorMessage = ""
    @State private var isLoading = false
    
    var userId: Int {
        UserDefaults.standard.integer(forKey: "userId")
    }
    
    var body: some View {
        NavigationView {
            Form {
                Section {
                    AsyncImage(url: URL(string: product.imagem)) { image in
                        image
                            .resizable()
                            .aspectRatio(contentMode: .fit)
                    } placeholder: {
                        Color.gray
                    }
                    .frame(maxHeight: 200)
                    
                    Text(product.nome)
                        .font(.headline)
                    
                    Text("€\(String(format: "%.2f", product.preco))")
                        .font(.subheadline)
                        .foregroundColor(.blue)
                }
                
                Section {
                    Stepper("Quantidade: \(selectedQuantity)", value: $selectedQuantity, in: 1...10)
                        .disabled(isLoading)
                }
                
                Section {
                    Button(action: {
                        Task {
                            await addToCart()
                        }
                    }) {
                        if isLoading {
                            ProgressView()
                                .progressViewStyle(CircularProgressViewStyle())
                        } else {
                            Text("Adicionar ao Carrinho")
                        }
                    }
                    .disabled(isLoading)
                }
            }
            .navigationTitle("Adicionar ao Carrinho")
            .navigationBarItems(trailing: Button("Cancelar") {
                dismiss()
            })
            .alert("Erro", isPresented: $showError) {
                Button("OK", role: .cancel) {}
            } message: {
                Text(errorMessage)
            }
        }
    }
    
    private func addToCart() async {
        isLoading = true
        
        do {
            print("Adicionando ao carrinho - Usuário: \(userId), SKU: \(product.codigo_base), Quantidade: \(selectedQuantity)")
            _ = try await APIService.shared.addToCart(
                id_utilizador: userId,
                session_id: cartManager.sessionId,
                sku: product.codigo_base,
                quantidade: selectedQuantity
            )
            print("Item adicionado ao carrinho com sucesso")
            await MainActor.run {
                dismiss()
            }
        } catch APIError.serverError(let message) {
            print("Erro do servidor ao adicionar ao carrinho: \(message)")
            await MainActor.run {
                showError = true
                errorMessage = message
                isLoading = false
            }
        } catch APIError.networkError(let message) {
            print("Erro de rede ao adicionar ao carrinho: \(message)")
            await MainActor.run {
                showError = true
                errorMessage = "Erro de conexão. Verifique sua internet e tente novamente."
                isLoading = false
            }
        } catch {
            print("Erro inesperado ao adicionar ao carrinho: \(error)")
            await MainActor.run {
                showError = true
                errorMessage = "Erro ao adicionar ao carrinho. Por favor, tente novamente."
                isLoading = false
            }
        }
    }
}

#Preview {
    ProductsView()
}