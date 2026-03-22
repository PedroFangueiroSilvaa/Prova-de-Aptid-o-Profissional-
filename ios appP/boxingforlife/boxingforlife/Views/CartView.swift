

import SwiftUI


struct CartView: View {
 
    @EnvironmentObject var cartManager: CartManager
    
  
    @State private var isProcessing = false
    @State private var showAlert = false
    @State private var alertMessage = ""
    
    
    var body: some View {
       
        NavigationView {
            VStack {
                
                if cartManager.cartItems.isEmpty {
                    VStack(spacing: 20) {
                        
                        Image(systemName: "cart")
                            .font(.system(size: 60))
                            .foregroundColor(.gray)
                        
                        
                        Text("Seu carrinho está vazio")
                            .font(.title2)
                            .foregroundColor(.gray)
                    }
                    .padding()
                } 
                
                else {
                    
                    List {
                       
                        ForEach(cartManager.cartItems) { item in
                            
                            CartItemRow(item: item)
                        }
                        
                        .onDelete { indexSet in
                            for index in indexSet {
                                if index < cartManager.cartItems.count {
                                    let item = cartManager.cartItems[index]
                                    cartManager.removeFromCart(item: item)
                                }
                            }
                        }
                        
                        
                        Section {
                            HStack {
                               
                                Text("Total")
                                    .font(.headline)
                                
                                
                                Spacer()
                                
                                
                                Text("€\(cartManager.total, specifier: "%.2f")")
                                    .font(.headline)
                            }
                        }
                    }
                    
                    
                    NavigationLink(destination: CheckoutView(cartManager: cartManager)) {
                        
                        Text("Finalizar Compra")
                            .frame(maxWidth: .infinity)
                            .padding()
                            .background(Color.blue)
                            .foregroundColor(.white)
                            .cornerRadius(10)
                    }
                    .padding()
                }
            }
            
            .navigationTitle("Carrinho")
            
            
            .alert("Aviso", isPresented: $showAlert) {
                Button("OK", role: .cancel) { }
            } message: {
                Text(alertMessage)
            }
            
            
            .onAppear {
                Task {
                    await cartManager.getCartItems()
                }
            }
        }
    }
}


#Preview {
    CartView()
        .environmentObject(CartManager())
} 