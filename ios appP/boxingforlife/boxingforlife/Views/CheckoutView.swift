
import SwiftUI
import StripePaymentSheet


struct CheckoutView: View {
   
    @ObservedObject var cartManager: CartManager
    
    
    @StateObject private var orderProcessor = OrderProcessor()
    
   
    @StateObject private var authManager = AuthManager.shared
    
 
    @State private var isProcessing = false
    @State private var showAlert = false
    @State private var alertMessage = ""
    @State private var local_envio = ""
    @State private var showingLogin = false
    @State private var isLoadingAddress = false
    @State private var isPreparingPayment = false
    @State private var showSuccessView = false
    
 
    private var userId: Int {
        UserDefaults.standard.integer(forKey: "userId")
    }
    
  
    var body: some View {
        
        Group {
           
            if showSuccessView {
                orderSuccessView
            } 
           
            else if !authManager.isLoggedIn {
                loginPromptView
            } 
           
            else {
                checkoutContentView
            }
        }
        .onChange(of: authManager.isLoggedIn) { isLoggedIn in
            if isLoggedIn {
                cartManager.cartItems = []
                
                Task {
                    await cartManager.getCartItems()
                    await fetchUserAddressFromAPI()
                }
            }
        }
        // Sheet para mostrar a tela de confirmação de compra
        .sheet(isPresented: $orderProcessor.showOrderCompleted) {
            OrderCompletedView(
                orderDetails: orderProcessor.completedOrderDetails,
                purchasedItems: orderProcessor.completedOrderItems
            )
            .environmentObject(cartManager)
            .environmentObject(orderProcessor)
        }
    }
    
    
    private var orderSuccessView: some View {
        VStack(spacing: 20) {
           
            Image(systemName: "checkmark.circle.fill")
                .resizable()
                .scaledToFit()
                .frame(width: 100, height: 100)
                .foregroundColor(.green)
            
            
            Text("Pagamento concluído com sucesso!")
                .font(.title)
                .fontWeight(.bold)
           
            Text("O seu pedido foi confirmado e será processado em breve.")
                .font(.body)
                .multilineTextAlignment(.center)
                .padding()
            
          
            Button("Voltar às Compras") {
                showSuccessView = false
            }
            .buttonStyle(.borderedProminent)
        }
        .padding()
        .navigationTitle("Pedido Confirmado")
    }
    
  
    private var loginPromptView: some View {
        VStack(spacing: 20) {
        
            Text("É necessário iniciar sessão para finalizar a compra")
                .font(.headline)
                .multilineTextAlignment(.center)
                .padding()
            
           
            Button("Iniciar Sessão") {
                showingLogin = true
            }
            .buttonStyle(.borderedProminent)
        }
        
        .sheet(isPresented: $showingLogin) {
            LoginView()
        }
    }
    
 
    private var checkoutContentView: some View {
        
        VStack {
         
            List {
              
                ForEach(cartManager.cartItems) { item in
                    CartItemRow(item: item)
                }

             
                Section {
                    Text("Total: \(String(format: "%.2f", cartManager.total)) €")
                        .font(.headline)
                }

             
                Section(header: Text("Endereço de Envio")) {
                  
                    if isLoadingAddress {
                        HStack {
                            ProgressView()
                                .progressViewStyle(CircularProgressViewStyle())
                            Text("Carregando endereço...")
                                .font(.subheadline)
                                .padding(.leading, 8)
                        }
                    } 
                   
                    else if !local_envio.isEmpty {
                        Text(local_envio)
                            .font(.subheadline)
                            .padding(.vertical, 4)
                    } 
                    
                    else {
                        Text("Nenhum endereço de envio encontrado.")
                            .foregroundColor(.red)
                            .font(.subheadline)
                    }
                }
                
                
                Section(header: Text("Pagamento")) {
                    
                    if isPreparingPayment {
                        HStack {
                            ProgressView()
                            Text("Preparando pagamento...")
                                .padding(.leading, 8)
                        }
                    } 
                    
                    else if orderProcessor.isPaymentReady, let paymentSheet = orderProcessor.paymentSheet {
                        
                        PaymentSheet.PaymentButton(
                            paymentSheet: paymentSheet,
                            onCompletion: { result in
                                orderProcessor.handlePaymentResult(result)
                                if case .completed = result {
                                    Task {
                                        await processPayment()
                                    }
                                }
                            }
                        ) {
                            
                            HStack {
                                Image(systemName: "creditcard")
                                Text("Pagar com Cartão")
                            }
                            .frame(maxWidth: .infinity)
                            .padding()
                            .background(Color.blue)
                            .foregroundColor(.white)
                            .cornerRadius(10)
                        }
                    } 
                   
                    else {
                        Button(action: {
                            Task {
                                await preparePayment()
                            }
                        }) {
                            HStack {
                                Image(systemName: "creditcard")
                                Text("Preparar Pagamento")
                            }
                            .frame(maxWidth: .infinity)
                            .padding()
                        }
                        
                        .buttonStyle(.borderedProminent)
                        .disabled(local_envio.isEmpty || cartManager.cartItems.isEmpty)
                    }
                }
            }
        }
        
        .onAppear {
            
            print("[CheckoutView] authManager.currentUser: \(String(describing: authManager.currentUser))")
            
            
            Task {
                await fetchUserAddressFromAPI()
            }
        }
        
        .navigationTitle("Checkout")
        
       
        .alert("Aviso", isPresented: $showAlert) {
            Button("OK", role: .cancel) { }
        } message: {
            Text(alertMessage)
        }
        
        
        .refreshable {
            Task {
                await fetchUserAddressFromAPI()
            }
        }
    }
    
    
    private func preparePayment() async {
        
        isPreparingPayment = true
        
        
        defer { isPreparingPayment = false }
        
       
        let success = await orderProcessor.preparePayment(
            userId: userId,
            items: cartManager.cartItems,
            total: cartManager.total,
            address: local_envio
        )
        
        
        if !success {
            alertMessage = "Não foi possível preparar o pagamento. Tente novamente."
            showAlert = true
        }
    }
    
    // Função para buscar diretamente o endereço do usuário da API
    private func fetchUserAddressFromAPI() async {
        guard authManager.isLoggedIn, userId > 0 else { return }
        
        isLoadingAddress = true
        defer { isLoadingAddress = false }
        
        do {
            print("[CheckoutView] Buscando endereço diretamente da API para o usuário \(userId)")
            let userProfile = try await APIService.shared.getUserProfile(id: userId)
            
            await MainActor.run {
                if let address = userProfile["local_envio"] as? String, !address.isEmpty {
                    print("[CheckoutView] Endereço obtido da API: \(address)")
                    self.local_envio = address
                    
                    // Também atualizamos o AuthManager para manter a consistência
                    if let currentUser = authManager.currentUser {
                        var updatedUser = currentUser
                        updatedUser.shippingAddress = address
                        authManager.updateUserWithAddress(user: updatedUser, address: address)
                    }
                } else {
                    print("[CheckoutView] Nenhum endereço encontrado na API")
                    // Verificamos se já temos um endereço localmente
                    if let savedAddress = authManager.getUserShippingAddress(), !savedAddress.isEmpty {
                        self.local_envio = savedAddress
                    } else {
                        self.local_envio = ""
                    }
                }
            }
        } catch {
            print("[CheckoutView] Erro ao buscar endereço: \(error)")
            // Em caso de erro, tentamos usar o endereço do AuthManager
            if let savedAddress = authManager.getUserShippingAddress(), !savedAddress.isEmpty {
                await MainActor.run {
                    self.local_envio = savedAddress
                }
            }
        }
    }
    
    private func processPayment() async {
        isProcessing = true
        defer { isProcessing = false }
        
        // Verificar se temos um endereço de envio válido
        guard !local_envio.isEmpty else {
            alertMessage = "Por favor, adicione um endereço de envio antes de finalizar a compra."
            showAlert = true
            return
        }
        
        do {
            print("Iniciando processamento do pedido...")
            print("ID do usuário: \(userId)")
            print("Itens do carrinho: \(cartManager.cartItems)")
            print("Total: \(cartManager.total)")
            print("Endereço de envio: \(local_envio)")
            
            // Processando o pedido no servidor
            let response = try await orderProcessor.createOrder(
                userId: userId,
                items: cartManager.cartItems,
                total: cartManager.total,
                local_envio: local_envio
            )
            
            print("Resposta do servidor: \(response)")
            
            if let success = response["success"] as? Bool, success {
                cartManager.clearCart()
                showSuccessView = true
            } else {
                let errorMessage = response["error"] as? String ?? "Erro desconhecido"
                alertMessage = "Erro ao processar pedido: \(errorMessage)"
                showAlert = true
            }
        } catch {
            print("Erro ao processar pedido: \(error)")
            alertMessage = "Erro ao processar pedido: \(error.localizedDescription)"
            showAlert = true
        }
    }
}

#Preview {
    NavigationView {
        CheckoutView(
            cartManager: CartManager()
        )
    }
}