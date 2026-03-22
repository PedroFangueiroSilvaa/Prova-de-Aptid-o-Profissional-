import SwiftUI

struct OrderReviewView: View {
    let order: Order
    let orderDetail: [String: Any]
    let itens: [[String: Any]]
    @Environment(\.dismiss) private var dismiss
    @State private var orderRating = 0
    @State private var orderComment = ""
    @State private var productReviews: [String: ProductReviewInput] = [:]
    @State private var isSubmitting = false
    @State private var showError = false
    @State private var errorMessage = ""
    @State private var showSuccess = false
    @State private var hasOrderReview = false
    @State private var hasProductReviews: [String: Bool] = [:]
    @State private var isLoadingReviewStatus = true
    
    var currentUserId: Int {
        // Primeiro tenta obter do AuthManager
        if let authUserId = AuthManager.shared.userId {
            print("🔍 ID do utilizador obtido do AuthManager: \(authUserId)")
            return authUserId
        }
        
        // Depois tenta do UserDefaults
        let userDefaultsId = UserDefaults.standard.integer(forKey: "current_user_id")
        if userDefaultsId > 0 {
            print("🔍 ID do utilizador obtido do UserDefaults: \(userDefaultsId)")
            return userDefaultsId
        }
        
        // Fallback para userId genérico
        let fallbackId = UserDefaults.standard.integer(forKey: "userId")
        print("⚠️ Usando fallback ID: \(fallbackId)")
        return fallbackId
    }
    
    var body: some View {
        NavigationView {
            ScrollView {
                VStack(alignment: .leading, spacing: 20) {
                    // Cabeçalho
                    headerSection
                    
                    // Avaliação da encomenda
                    orderReviewSection
                    
                    // Avaliações dos produtos
                    if !itens.isEmpty {
                        productsReviewSection
                    }
                    
                    // Botão de submissão
                    submitButton
                }
                .padding()
            }
            .navigationTitle("Avaliar Encomenda")
            .navigationBarTitleDisplayMode(.inline)
            .toolbar {
                ToolbarItem(placement: .navigationBarTrailing) {
                    Button("Cancelar") {
                        dismiss()
                    }
                }
            }
            .alert("Erro", isPresented: $showError) {
                Button("OK", role: .cancel) {}
            } message: {
                Text(errorMessage)
            }
            .alert("Sucesso", isPresented: $showSuccess) {
                Button("OK", role: .cancel) {
                    dismiss()
                }
            } message: {
                Text("Avaliações submetidas com sucesso!")
            }
            .onAppear {
                Task {
                    await checkExistingReviews()
                }
            }
        }
        .onAppear {
            Task {
                await checkExistingReviews()
            }
        }
    }
    
    private var headerSection: some View {
        VStack(alignment: .leading, spacing: 8) {
            Text("Encomenda #\(orderDetail["id_encomenda"] as? Int ?? order.id)")
                .font(.title2)
                .bold()
            
            if let data = orderDetail["data_encomenda"] as? String {
                Text("Data: \(formatDate(data))")
                    .font(.subheadline)
                    .foregroundColor(.gray)
            }
        }
    }
    
    private var orderReviewSection: some View {
        VStack(alignment: .leading, spacing: 12) {
            Text("Avaliação da Encomenda")
                .font(.headline)
            
            if hasOrderReview {
                Text("Você já avaliou esta encomenda")
                    .font(.subheadline)
                    .foregroundColor(.orange)
                    .padding()
                    .background(Color.orange.opacity(0.1))
                    .cornerRadius(8)
            } else {
                Text("Como foi a sua experiência com esta encomenda?")
                    .font(.subheadline)
                    .foregroundColor(.gray)
                
                // Rating stars
                HStack(spacing: 8) {
                    ForEach(1...5, id: \.self) { star in
                        Button(action: {
                            orderRating = star
                        }) {
                            Image(systemName: star <= orderRating ? "star.fill" : "star")
                                .foregroundColor(.yellow)
                                .font(.system(size: 24))
                        }
                    }
                }
                
                // Comentário
                VStack(alignment: .leading, spacing: 4) {
                    Text("Comentário (opcional)")
                        .font(.subheadline)
                    
                    TextEditor(text: $orderComment)
                        .frame(height: 80)
                        .padding(8)
                        .background(Color(.systemGray6))
                        .cornerRadius(8)
                }
            }
        }
        .padding()
        .background(Color(.systemBackground))
        .cornerRadius(12)
        .shadow(radius: 2)
    }
    
    private var productsReviewSection: some View {
        VStack(alignment: .leading, spacing: 16) {
            Text("Avaliação dos Produtos")
                .font(.headline)
            
            ForEach(itens.indices, id: \.self) { index in
                let item = itens[index]
                if let codigoBase = getCodigoBase(from: item) {
                    ProductReviewInputView(
                        item: item,
                        review: binding(for: codigoBase),
                        hasExistingReview: hasProductReviews[codigoBase] ?? false
                    )
                }
            }
        }
    }
    
    private var submitButton: some View {
        VStack(spacing: 12) {
            if isLoadingReviewStatus {
                HStack {
                    ProgressView()
                        .progressViewStyle(CircularProgressViewStyle())
                        .scaleEffect(0.8)
                    Text("Verificando status das avaliações...")
                        .font(.subheadline)
                        .foregroundColor(.gray)
                }
                .padding()
            } else {
                let canSubmitOrder = !hasOrderReview && orderRating > 0
                let canSubmitProducts = productReviews.contains { (codigoBase, review) in
                    review.rating > 0 && !(hasProductReviews[codigoBase] ?? false)
                }
                let canSubmit = canSubmitOrder || canSubmitProducts
                
                Button(action: {
                    Task {
                        await submitReviews()
                    }
                }) {
                    HStack {
                        if isSubmitting {
                            ProgressView()
                                .progressViewStyle(CircularProgressViewStyle(tint: .white))
                                .scaleEffect(0.8)
                        } else {
                            Text("Submeter Avaliações")
                                .font(.headline)
                        }
                    }
                    .foregroundColor(.white)
                    .frame(maxWidth: .infinity)
                    .padding()
                    .background(isSubmitting || !canSubmit ? Color.gray : Color.blue)
                    .cornerRadius(12)
                }
                .disabled(isSubmitting || !canSubmit)
                
                if !canSubmit && !isSubmitting {
                    Text("Nenhuma nova avaliação disponível para submeter")
                        .font(.caption)
                        .foregroundColor(.gray)
                        .padding(.top, 8)
                }
            }
        }
    }
    
    private func binding(for codigoBase: String) -> Binding<ProductReviewInput> {
        return Binding(
            get: {
                productReviews[codigoBase] ?? ProductReviewInput()
            },
            set: { newValue in
                productReviews[codigoBase] = newValue
            }
        )
    }
    
    private func getCodigoBase(from item: [String: Any]) -> String? {
        return item["codigo_base"] as? String
    }
    
    private func formatDate(_ dateString: String) -> String {
        let formatter = DateFormatter()
        formatter.dateFormat = "yyyy-MM-dd HH:mm:ss"
        
        if let date = formatter.date(from: dateString) {
            let displayFormatter = DateFormatter()
            displayFormatter.dateStyle = .medium
            displayFormatter.timeStyle = .none
            displayFormatter.locale = Locale(identifier: "pt_PT")
            return displayFormatter.string(from: date)
        }
        
        return dateString
    }
    
    private func submitReviews() async {
        isSubmitting = true
        
        do {
            var successCount = 0
            var errorMessages: [String] = []
            
            // 1. Submeter review da encomenda (se fornecida)
            if orderRating > 0 && !hasOrderReview {
                do {
                    try await APIService.shared.submitOrderReview(
                        idEncomenda: order.id,
                        idUtilizador: currentUserId,
                        classificacao: orderRating,
                        comentario: orderComment
                    )
                    successCount += 1
                    print("✅ Review da encomenda submetida com sucesso")
                } catch {
                    errorMessages.append("Erro ao avaliar encomenda: \(error.localizedDescription)")
                    print("❌ Erro ao submeter review da encomenda: \(error)")
                }
            }
            
            // 2. Submeter reviews dos produtos (independentemente da encomenda)
            for (codigoBase, review) in productReviews {
                if review.rating > 0 && !(hasProductReviews[codigoBase] ?? false) {
                    do {
                        try await APIService.shared.submitProductReview(
                            idEncomenda: order.id,
                            codigoBase: codigoBase,
                            idUtilizador: currentUserId,
                            classificacao: review.rating,
                            comentario: review.comment
                        )
                        successCount += 1
                        print("✅ Review do produto \(codigoBase) submetida com sucesso")
                        
                        // Notificar que uma review foi submetida para atualizar a UI
                        NotificationCenter.default.post(
                            name: .reviewSubmitted,
                            object: codigoBase
                        )
                    } catch {
                        errorMessages.append("Erro ao avaliar produto \(codigoBase): \(error.localizedDescription)")
                        print("❌ Erro ao submeter review do produto \(codigoBase): \(error)")
                    }
                }
            }
            
            // 3. Mostrar resultado final
            if successCount > 0 {
                if errorMessages.isEmpty {
                    // Todas as submissões foram bem-sucedidas
                    showSuccess = true
                } else {
                    // Algumas falharam, mostrar erro com detalhes
                    errorMessage = "Algumas avaliações foram submetidas com sucesso, mas: \(errorMessages.joined(separator: ", "))"
                    showError = true
                }
            } else {
                // Nenhuma submissão foi bem-sucedida
                if errorMessages.isEmpty {
                    errorMessage = "Nenhuma avaliação foi fornecida"
                } else {
                    errorMessage = errorMessages.joined(separator: ", ")
                }
                showError = true
            }
            
        } catch {
            errorMessage = "Erro inesperado: \(error.localizedDescription)"
            showError = true
        }
        
        isSubmitting = false
    }
    
    private func checkExistingReviews() async {
        isLoadingReviewStatus = true
        
        do {
            // Verificar se já existe review da encomenda
            hasOrderReview = try await APIService.shared.hasOrderReview(
                idEncomenda: order.id,
                idUtilizador: currentUserId
            )
            
            // Verificar reviews dos produtos
            for item in itens {
                if let codigoBase = getCodigoBase(from: item) {
                    let hasReview = try await APIService.shared.hasProductReview(
                        idEncomenda: order.id,
                        codigoBase: codigoBase, 
                        idUtilizador: currentUserId
                    )
                    hasProductReviews[codigoBase] = hasReview
                }
            }
            
            print("✅ Status das reviews carregado - Encomenda: \(hasOrderReview), Produtos: \(hasProductReviews)")
            
            // Forçar atualização da UI
            await MainActor.run {
                // Trigger uma atualização da interface para refletir o estado correto
                isLoadingReviewStatus = false
                isLoadingReviewStatus = true
            }
            
        } catch {
            print("❌ Erro ao verificar status das reviews: \(error)")
            // Em caso de erro, permitir submissão
            hasOrderReview = false
            hasProductReviews = [:]
        }
        
        isLoadingReviewStatus = false
    }
}

struct ProductReviewInput {
    var rating: Int = 0
    var comment: String = ""
}

struct ProductReviewInputView: View {
    let item: [String: Any]
    @Binding var review: ProductReviewInput
    let hasExistingReview: Bool
    
    var codigoBase: String? {
        return item["codigo_base"] as? String
    }
    
    var body: some View {
        VStack(alignment: .leading, spacing: 8) {
            // Informações do produto
            HStack {
                if let img = item["imagem"] as? String, !img.isEmpty,
                   let url = URL(string: APIService.shared.processImageURL(img)) {
                    AsyncImage(url: url) { phase in
                        switch phase {
                        case .empty:
                            ProgressView()
                        case .success(let image):
                            image
                                .resizable()
                                .aspectRatio(contentMode: .fill)
                        case .failure:
                            Image(systemName: "photo")
                        @unknown default:
                            EmptyView()
                        }
                    }
                    .frame(width: 60, height: 60)
                    .cornerRadius(8)
                } else {
                    Image(systemName: "photo")
                        .frame(width: 60, height: 60)
                        .background(Color(.systemGray5))
                        .cornerRadius(8)
                }
                
                VStack(alignment: .leading, spacing: 4) {
                    Text(item["nome_produto"] as? String ?? "Produto")
                        .font(.headline)
                    
                    Text("\(item["tamanho"] as? String ?? "-") - \(item["cor"] as? String ?? "-")")
                        .font(.subheadline)
                        .foregroundColor(.gray)
                    
                    Text("Quantidade: \(item["quantidade"] as? Int ?? 0)")
                        .font(.caption)
                        .foregroundColor(.gray)
                }
                
                Spacer()
            }
            
            // Verificar se já foi avaliado
            if hasExistingReview {
                Text("✅ Você já avaliou este produto")
                    .font(.subheadline)
                    .foregroundColor(.green)
                    .padding()
                    .background(Color.green.opacity(0.1))
                    .cornerRadius(8)
            } else {
                // Rating
                HStack(spacing: 4) {
                    Text("Avaliação:")
                        .font(.subheadline)
                    
                    ForEach(1...5, id: \.self) { star in
                        Button(action: {
                            review.rating = star
                        }) {
                            Image(systemName: star <= review.rating ? "star.fill" : "star")
                                .foregroundColor(.yellow)
                                .font(.system(size: 18))
                        }
                    }
                }
                
                // Comentário
                if review.rating > 0 {
                    VStack(alignment: .leading, spacing: 4) {
                        Text("Comentário (opcional)")
                            .font(.caption)
                            .foregroundColor(.gray)
                        
                        TextField("O que achou deste produto?", text: $review.comment, axis: .vertical)
                            .textFieldStyle(RoundedBorderTextFieldStyle())
                            .lineLimit(3)
                    }
                }
            }
        }
        .padding()
        .background(Color(.systemGray6))
        .cornerRadius(8)
    }
}

#Preview {
    OrderReviewView(
        order: Order(
            id: 1, 
            id_utilizador: 1, 
            items: [], 
            total: 50.0, 
            local_envio: "Rua Exemplo, 123", 
            data_encomenda: "2024-01-01 10:00:00", 
            status: "pago"
        ),
        orderDetail: ["id_encomenda": 1, "data_encomenda": "2024-01-01 10:00:00"],
        itens: []
    )
}
