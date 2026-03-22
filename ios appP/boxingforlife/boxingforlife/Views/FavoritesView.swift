
import SwiftUI


struct FavoritesView: View {
  
    @ObservedObject private var favoritesManager = FavoritesManager.shared
    @State private var products: [[String: Any]] = []
    @State private var isLoadingProducts = false
    @State private var showError = false
    @State private var errorMessage = ""
    var body: some View {
        NavigationView {
            Group {
               
                if isLoadingProducts || favoritesManager.isLoading {
                    ProgressView("Carregando favoritos...")
                        .frame(maxWidth: .infinity, maxHeight: .infinity)
                } 
              
                else if favoriteProducts.isEmpty {
                    VStack(spacing: 20) {
                       
                        Image(systemName: "heart")
                            .font(.system(size: 60))
                            .foregroundColor(.gray)
                        
                     
                        Text("Ainda não tem produtos favoritos")
                            .font(.title2)
                            .foregroundColor(.gray)
                        
                       
                        Button("Sincronizar favoritos") {
                            Task {
                                await favoritesManager.syncWithServer()
                            }
                        }
                        .padding()
                        .background(Color.blue)
                        .foregroundColor(.white)
                        .cornerRadius(8)
                    }
                    .padding()
                } 
             
                else {
                    ScrollView {
                      
                        LazyVStack(spacing: 16) {
                           
                            ForEach(favoriteProducts.indices, id: \.self) { index in
                                let product = favoriteProducts[index]
                                ProductRow(product: product)
                            }
                        }
                        .padding()
                    }
                    
                    .refreshable {
                        await loadProducts()
                        await favoritesManager.syncWithServer()
                    }
                }
            }
            
            .navigationTitle("Favoritos")
            
           
            .alert("Erro", isPresented: $showError) {
                Button("OK", role: .cancel) {}
            } message: {
                Text(errorMessage)
            }
            
            
            .onAppear {
                loadProducts()
                Task {
                    await favoritesManager.syncWithServer()
                }
            }
            
            
            .onChange(of: favoritesManager.error) { error in
                if let error = error {
                    errorMessage = error
                    showError = true
                }
            }
        }
    }
    
    
    private var favoriteProducts: [[String: Any]] {
        products.filter { product in
            if let codigo = product["codigo_base"] as? String {
                return favoritesManager.isFavorite(codigoBase: codigo)
            }
            return false
        }
    }
    
    
    private func loadProducts() {
        isLoadingProducts = true
        Task {
            do {
                
                let fetchedProducts = try await APIService.shared.getProducts()
                
                
                await MainActor.run {
                    products = fetchedProducts
                    isLoadingProducts = false
                }
            } catch {
                
                await MainActor.run {
                    errorMessage = "Erro ao carregar produtos: \(error.localizedDescription)"
                    showError = true
                    isLoadingProducts = false
                }
            }
        }
    }
} 