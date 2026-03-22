

import SwiftUI
struct ContentView: View {
   
    @StateObject private var cartManager = CartManager()
    
    
    @StateObject private var authManager = AuthManager.shared
    
    
    @StateObject private var apiService = APIService.shared
    
    
    @State private var showingLogin = false
    
   
    var body: some View {
        // NavigationView permite navegação hierárquica entre telas
        // É o contentor principal que permite push/pop de Views
        NavigationView {
            // TabView cria os separadores na parte inferior da tela
            // Permite navegação horizontal entre secções principais
            TabView {
                // SEPARADOR 1: "Início" - tela inicial da app
                HomeView()
                    .tabItem {
                        // Label combina ícone + texto do separador
                        Label("Início", systemImage: "house")
                    }
                
                // SEPARADOR 2: "Produtos" - catálogo de produtos
                ProductsView()
                    .tabItem {
                        Label("Produtos", systemImage: "bag")
                    }
                
                // SEPARADOR 3: "Favoritos" - produtos marcados como favoritos
                FavoritesView()
                    .tabItem {
                        Label("Favoritos", systemImage: "heart")
                    }
                
                // SEPARADOR 4: "Carrinho" - carrinho de compras
                CartView()
                    .tabItem {
                        Label("Carrinho", systemImage: "cart")
                    }
                
                // SEPARADOR 5: "Perfil" - só aparece se utilizador estiver logado
                // Esta condição esconde/mostra dinamicamente o separador baseado no login
                if authManager.isLoggedIn {
                    ProfileView()
                        .tabItem {
                            Label("Perfil", systemImage: "person")
                        }
                }
            }
            // Aplica a cor laranja aos ícones dos separadores selecionados
            // AppTheme.primaryOrange define a cor de destaque da app
            .accentColor(AppTheme.primaryOrange)
            
            // Configura a barra superior (navigation bar) com informações do utilizador
            .toolbar {
                // ToolbarItem define onde colocar elementos na barra
                // .navigationBarTrailing = lado direito da barra
                ToolbarItem(placement: .navigationBarTrailing) {
                    // Lógica condicional: mostra conteúdo diferente baseado no estado de login
                    
                    // CASO 1: Utilizador está logado
                    if authManager.isLoggedIn {
                        HStack {
                            // Nome do utilizador logado
                            // authManager.currentUser?.nome obtém o nome do utilizador atual
                            // ?? "" fornece string vazia se nome for nil
                            Text(authManager.currentUser?.nome ?? "")
                                .font(.headline)                    // Fonte em destaque
                                .foregroundColor(AppTheme.text)     // Cor do texto do tema
                            
                            // Botão de logout com ícone de sair
                            Button(action: {
                                // authManager.signOut() faz logout e limpa dados
                                authManager.signOut()
                            }) {
                                // Ícone de sair (pessoa + seta)
                                Image(systemName: "rectangle.portrait.and.arrow.right")
                                    .foregroundColor(AppTheme.error)    // Cor vermelha para logout
                            }
                        }
                    } else {
                        // CASO 2: Utilizador NÃO está logado
                        // Mostra botão "Entrar" para abrir modal de login
                        Button("Entrar") {
                            // Altera o estado para mostrar o modal de login
                            showingLogin = true
                        }
                        .foregroundColor(AppTheme.primaryOrange)    // Cor laranja do tema
                    }
                }
            }
            
            
            .sheet(isPresented: $showingLogin) {
                LoginView()
            }
        }
        
        
        .sheet(isPresented: $showingLogin) {
            LoginView()
        }
        
       
        
        // Disponibiliza o CartManager para todas as telas filhas
        .environmentObject(cartManager)
        
        // Disponibiliza o AuthManager para todas as telas filhas  
        .environmentObject(authManager)
        
        // Aplica o tema laranja personalizado em toda a aplicação
        // Este modificador está definido em Theme/ e aplica cores consistentes
        .orangeThemeStyle()
    }
}


#Preview {
    ContentView()
}