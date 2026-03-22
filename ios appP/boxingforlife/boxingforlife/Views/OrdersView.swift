import SwiftUI


struct OrdersView: View {
    
    @StateObject private var orderProcessor = OrderProcessor()
    
   
    @State private var isLoading = false
    @State private var showError = false
    @State private var errorMessage = ""
    @State private var selectedOrder: Order? = nil
    
    
    var body: some View {
        
        ZStack {
           
            if isLoading {
                ProgressView("Carregando encomendas...")
            } 
            
            else if orderProcessor.orders.isEmpty {
                VStack(spacing: 16) {
                    
                    Image(systemName: "box.truck")
                        .font(.system(size: 50))
                        .foregroundColor(.gray)
                    
                    
                    Text("Nenhuma encomenda encontrada")
                        .font(.headline)
                    
                    
                    Text("As suas encomendas aparecerão aqui")
                        .font(.subheadline)
                        .foregroundColor(.gray)
                }
            } 
            
            else {
                List {
                    
                    ForEach(0..<orderProcessor.orders.count, id: \.self) { index in
                        let order = orderProcessor.orders[index]
                        OrderRow(order: order)
                            
                            .contentShape(Rectangle())
                            
                            .onTapGesture {
                                selectedOrder = order
                            }
                    }
                }
            }
        }
       
        .navigationTitle("Minhas Encomendas")
        
        
        .onAppear {
            Task {
                await loadOrders()
            }
        }
        
       
        .refreshable {
            await loadOrders()
        }
        
       
        .sheet(item: $selectedOrder) { order in
            OrderDetailView(order: order)
        }
        
       
        .alert("Erro", isPresented: $showError) {
            Button("OK", role: .cancel) { }
        } message: {
            Text(errorMessage)
        }
    }
    
    
    private func loadOrders() async {
        
        isLoading = true
        
        
        defer { isLoading = false }
        
        do {
            
            let userId = UserDefaults.standard.integer(forKey: "userId")
            
            
            try await orderProcessor.getOrders(for: userId)
            
            
            print("===== ENCOMENDAS RECEBIDAS =====")
            var i = 0
            while i < orderProcessor.orders.count {
                let order = orderProcessor.orders[i]
                print("Encomenda #\(order.id):")
                print("- Data: \(order.data_encomenda ?? "nil")")
                print("- Status: \(order.status)")
                print("- Total: \(order.total)")
                if let items = order.items {
                    print("- Itens: \(items.count)")
                    for item in items {
                        print("  * \(item.nome ?? "Sem nome") - \(item.quantidade)x")
                    }
                } else {
                    print("- Sem itens")
                }
                print("------------------------")
                i += 1
            }
        } catch {
            
            print("Erro ao carregar encomendas: \(error)")
            errorMessage = "Erro ao carregar encomendas: \(error.localizedDescription)"
            showError = true
        }
    }
}


struct OrderRow: View {
   
    let order: Order
    
    
    var body: some View {
        VStack(alignment: .leading, spacing: 12) {
            
            HStack {
                
                Text("Encomenda #\(order.id)")
                    .font(.headline)
                
                Spacer()
                
               
                StatusBadge(status: order.orderStatus)
            }
            
            
            if let items = order.items {
                VStack(alignment: .leading, spacing: 8) {
                   
                    LazyVStack(spacing: 8) {
                        
                        ForEach(0..<min(2, items.count), id: \.self) { index in
                            OrderItemView(item: items[index])
                        }
                    }
                    
                   
                    if (items.count > 2) {
                        Text("+ \(items.count - 2) mais itens")
                            .font(.caption)
                            .foregroundColor(.gray)
                    }
                }
            }
            
           
            Divider()
            
            
            HStack {
                
                if let date = order.data_encomenda {
                    Text(formatDate(date) ?? "Data não disponível")
                        .font(.caption)
                        .foregroundColor(.gray)
                }
                
                
                Spacer()
                
               
                Text("Total: €\(order.total, specifier: "%.2f")")
                    .font(.headline)
            }
        }
        
        .padding(.vertical, 8)
    }
    
    
    private func formatDate(_ dateString: String) -> String? {
        print("Formatando data: \(dateString)")
        
        let formatter = DateFormatter()
        
        
        formatter.dateFormat = "yyyy-MM-dd'T'HH:mm:ss.SSSZ"
        formatter.locale = Locale(identifier: "pt_PT")
        
        if let date = formatter.date(from: dateString) {
            formatter.dateStyle = .medium
            formatter.timeStyle = .none
            let formattedDate = formatter.string(from: date)
            print("Data formatada com sucesso (ISO): \(formattedDate)")
            return formattedDate
        }
        
        print("Falha ao formatar data no formato ISO, tentando formato alternativo")
        
        
        formatter.dateFormat = "yyyy-MM-dd HH:mm:ss"
        if let date = formatter.date(from: dateString) {
            formatter.dateStyle = .medium
            formatter.timeStyle = .none
            let formattedDate = formatter.string(from: date)
            print("Data formatada com sucesso (alternativo): \(formattedDate)")
            return formattedDate
        }
        
        
        formatter.dateFormat = "yyyy-MM-dd'T'HH:mm:ssZ"
        if let date = formatter.date(from: dateString) {
            formatter.dateStyle = .medium
            formatter.timeStyle = .none
            let formattedDate = formatter.string(from: date)
            print("Data formatada com sucesso (ISO sem milissegundos): \(formattedDate)")
            return formattedDate
        }
        
        // Tentar formato alternativo sem timezone
        formatter.dateFormat = "yyyy-MM-dd'T'HH:mm:ss"
        if let date = formatter.date(from: dateString) {
            formatter.dateStyle = .medium
            formatter.timeStyle = .none
            let formattedDate = formatter.string(from: date)
            print("Data formatada com sucesso (ISO sem timezone): \(formattedDate)")
            return formattedDate
        }
        
        // Tentar formato alternativo com timezone no formato +00:00
        formatter.dateFormat = "yyyy-MM-dd'T'HH:mm:ss.SSS'+00:00'"
        if let date = formatter.date(from: dateString) {
            formatter.dateStyle = .medium
            formatter.timeStyle = .none
            let formattedDate = formatter.string(from: date)
            print("Data formatada com sucesso (ISO com timezone +00:00): \(formattedDate)")
            return formattedDate
        }
        
        // Tentar formato alternativo com timezone no formato Z
        formatter.dateFormat = "yyyy-MM-dd'T'HH:mm:ss.SSS'Z'"
        if let date = formatter.date(from: dateString) {
            formatter.dateStyle = .medium
            formatter.timeStyle = .none
            let formattedDate = formatter.string(from: date)
            print("Data formatada com sucesso (ISO com timezone Z): \(formattedDate)")
            return formattedDate
        }
        
        // Tentar formato alternativo com timezone no formato Z sem milissegundos
        formatter.dateFormat = "yyyy-MM-dd'T'HH:mm:ss'Z'"
        if let date = formatter.date(from: dateString) {
            formatter.dateStyle = .medium
            formatter.timeStyle = .none
            let formattedDate = formatter.string(from: date)
            print("Data formatada com sucesso (ISO com timezone Z sem milissegundos): \(formattedDate)")
            return formattedDate
        }
        
        // Tentar formato alternativo com timezone no formato +00:00 sem milissegundos
        formatter.dateFormat = "yyyy-MM-dd'T'HH:mm:ss'+00:00'"
        if let date = formatter.date(from: dateString) {
            formatter.dateStyle = .medium
            formatter.timeStyle = .none
            let formattedDate = formatter.string(from: date)
            print("Data formatada com sucesso (ISO com timezone +00:00 sem milissegundos): \(formattedDate)")
            return formattedDate
        }
        
        // Tentar formato alternativo com timezone no formato +00:00 sem milissegundos e sem segundos
        formatter.dateFormat = "yyyy-MM-dd'T'HH:mm'+00:00'"
        if let date = formatter.date(from: dateString) {
            formatter.dateStyle = .medium
            formatter.timeStyle = .none
            let formattedDate = formatter.string(from: date)
            print("Data formatada com sucesso (ISO com timezone +00:00 sem milissegundos e sem segundos): \(formattedDate)")
            return formattedDate
        }
        
        // Tentar formato alternativo com timezone no formato +00:00 sem milissegundos e sem segundos e sem minutos
        formatter.dateFormat = "yyyy-MM-dd'T'HH'+00:00'"
        if let date = formatter.date(from: dateString) {
            formatter.dateStyle = .medium
            formatter.timeStyle = .none
            let formattedDate = formatter.string(from: date)
            print("Data formatada com sucesso (ISO com timezone +00:00 sem milissegundos, segundos e minutos): \(formattedDate)")
            return formattedDate
        }
        
        // Tentar formato alternativo com timezone no formato +00:00 sem milissegundos e sem segundos e sem minutos e sem hora
        formatter.dateFormat = "yyyy-MM-dd'T'+00:00'"
        if let date = formatter.date(from: dateString) {
            formatter.dateStyle = .medium
            formatter.timeStyle = .none
            let formattedDate = formatter.string(from: date)
            print("Data formatada com sucesso (ISO com timezone +00:00 sem milissegundos, segundos, minutos e hora): \(formattedDate)")
            return formattedDate
        }
        
        print("Falha ao formatar data em qualquer formato")
        return nil
    }
}

// Componente para exibir um item da encomenda
struct OrderItemView: View {
    let item: OrderItem
    @State private var imageLoadError = false
    @State private var imageLoadAttempts = 0
    private let maxLoadAttempts = 3
    
    var body: some View {
        HStack(spacing: 12) {
            if let imageUrl = item.imagem, !imageUrl.isEmpty {
                if let processedUrl = URL(string: APIService.shared.processImageURL(imageUrl)) {
                    AsyncImage(url: processedUrl) { phase in
                        switch phase {
                        case .empty:
                            ProgressView()
                                .frame(width: 40, height: 40)
                        case .success(let image):
                            image
                                .resizable()
                                .aspectRatio(contentMode: .fill)
                                .frame(width: 40, height: 40)
                                .cornerRadius(6)
                        case .failure(let error):
                            Image(systemName: "photo")
                                .font(.system(size: 20))
                                .foregroundColor(.gray)
                                .frame(width: 40, height: 40)
                                .background(Color.gray.opacity(0.2))
                                .cornerRadius(6)
                                .onAppear {
                                    print("Erro ao carregar imagem: \(error.localizedDescription)")
                                    print("URL da imagem: \(processedUrl)")
                                    print("Tentativa \(imageLoadAttempts + 1) de \(maxLoadAttempts)")
                                    
                                    if imageLoadAttempts < maxLoadAttempts {
                                        imageLoadAttempts += 1
                                        DispatchQueue.main.asyncAfter(deadline: .now() + 1) {
                                            imageLoadError = true
                                            imageLoadError = false
                                        }
                                    }
                                }
                        @unknown default:
                            EmptyView()
                        }
                    }
                } else {
                    Image(systemName: "photo")
                        .font(.system(size: 20))
                        .foregroundColor(.gray)
                        .frame(width: 40, height: 40)
                        .background(Color.gray.opacity(0.2))
                        .cornerRadius(6)
                }
            } else {
                Image(systemName: "photo")
                    .font(.system(size: 20))
                    .foregroundColor(.gray)
                    .frame(width: 40, height: 40)
                    .background(Color.gray.opacity(0.2))
                    .cornerRadius(6)
            }
            
            VStack(alignment: .leading, spacing: 2) {
                Text(item.nome ?? "Produto")
                    .font(.subheadline)
                    .lineLimit(1)
                Text("\(item.quantidade)x - €\(item.preco_total, specifier: "%.2f")")
                    .font(.caption)
                    .foregroundColor(.gray)
            }
        }
    }
}

struct StatusBadge: View {
    let status: OrderStatus
    
    var body: some View {
        Text(status.displayName)
            .font(.caption)
            .padding(.horizontal, 8)
            .padding(.vertical, 4)
            .background(status.color.opacity(0.2))
            .foregroundColor(status.color)
            .cornerRadius(8)
    }
}

struct OrderDetailView: View {
    let order: Order
    @Environment(\.dismiss) private var dismiss
    @State private var isLoading = true
    @State private var errorMessage: String? = nil
    @State private var orderDetail: [String: Any]? = nil
    @State private var itens: [[String: Any]] = []

    var body: some View {
        NavigationView {
            Group {
                if isLoading {
                    ProgressView("Carregando detalhes...")
                } else if let errorMessage = errorMessage {
                    Text(errorMessage).foregroundColor(.red)
                } else if let orderDetail = orderDetail {
                    List {
                        Section {
                            HStack {
                                Text("Número")
                                Spacer()
                                Text("#\(orderDetail["id_encomenda"] as? Int ?? order.id)").bold()
                            }
                            HStack {
                                Text("Data")
                                Spacer()
                                if let data = orderDetail["data_encomenda"] as? String {
                                    Text(formatOrderDate(data) ?? data)
                                }
                            }
                            HStack {
                                Text("Status")
                                Spacer()
                                Text(orderDetail["status"] as? String ?? "-")
                            }
                            if let localEnvio = orderDetail["local_envio"] as? String {
                                HStack {
                                    Text("Endereço")
                                    Spacer()
                                    Text(localEnvio).multilineTextAlignment(.trailing)
                                }
                            }
                        } header: {
                            Text("Detalhes da Encomenda")
                        }
                        Section {
                            if !itens.isEmpty {
                                ForEach(itens.indices, id: \.self) { idx in
                                    let item = itens[idx]
                                    HStack(alignment: .top) {
                                        if let img = item["imagem"] as? String, !img.isEmpty, let url = URL(string: APIService.shared.processImageURL(img)) {
                                            AsyncImage(url: url) { phase in
                                                switch phase {
                                                case .empty: ProgressView().frame(width: 50, height: 50)
                                                case .success(let image): image.resizable().aspectRatio(contentMode: .fill).frame(width: 50, height: 50).cornerRadius(8)
                                                case .failure: Image(systemName: "photo").frame(width: 50, height: 50)
                                                @unknown default: EmptyView()
                                                }
                                            }
                                        } else {
                                            Image(systemName: "photo").frame(width: 50, height: 50)
                                        }
                                        VStack(alignment: .leading, spacing: 2) {
                                            Text(item["nome_produto"] as? String ?? "Produto")
                                                .font(.headline)
                                            Text("\(item["tamanho"] as? String ?? "-") - \(item["cor"] as? String ?? "-")")
                                                .font(.subheadline).foregroundColor(.gray)
                                            Text("SKU: \(item["sku"] as? String ?? "-")")
                                                .font(.caption2).foregroundColor(.gray)
                                            Text("\(item["quantidade"] ?? 0)x €\(String(format: "%.2f", (item["preco_unitario"] as? Double ?? 0)))")
                                                .font(.subheadline)
                                        }
                                        Spacer()
                                        Text("€\(String(format: "%.2f", (item["preco_unitario"] as? Double ?? 0) * Double(item["quantidade"] as? Int ?? 0)))")
                                            .font(.headline)
                                    }
                                }
                            } else {
                                Text("Nenhum produto encontrado.")
                            }
                        } header: {
                            Text("Produtos da Encomenda")
                        }
                        Section {
                            HStack {
                                Text("Subtotal")
                                Spacer()
                                Text("€\(String(format: "%.2f", getTotal(from: orderDetail)))")
                            }
                            HStack {
                                Text("Total").font(.headline)
                                Spacer()
                                Text("€\(String(format: "%.2f", getTotal(from: orderDetail)))").font(.headline)
                            }
                        } header: {
                            Text("Resumo")
                        }
                        
                        // Seção de avaliação
                        if canReviewOrder(orderDetail) {
                            Section {
                                NavigationLink(destination: OrderReviewView(order: order, orderDetail: orderDetail, itens: itens)) {
                                    HStack {
                                        Image(systemName: "star")
                                            .foregroundColor(.yellow)
                                        Text("Avaliar Encomenda e Produtos")
                                            .foregroundColor(.blue)
                                        Spacer()
                                        Image(systemName: "chevron.right")
                                            .foregroundColor(.gray)
                                            .font(.caption)
                                    }
                                }
                            } header: {
                                Text("Avaliação")
                            }
                        }
                    }
                }
            }
            .navigationTitle("Detalhes da Encomenda")
            .navigationBarTitleDisplayMode(.inline)
            .toolbar {
                ToolbarItem(placement: .navigationBarTrailing) {
                    Button("Fechar") { dismiss() }
                }
            }
            .onAppear {
                Task {
                    await fetchOrderDetail()
                }
            }
        }
    }

    private func fetchOrderDetail() async {
        isLoading = true
        errorMessage = nil
        do {
            let userId = UserDefaults.standard.integer(forKey: "userId")
            print("🔍 fetchOrderDetail: Buscando detalhes da encomenda #\(order.id) para usuário #\(userId)")
            
            let detail = try await APIService.shared.getOrderDetail(id_encomenda: order.id, id_utilizador: userId)
            print("✅ fetchOrderDetail: Dados recebidos: \(detail)")
            
            // Ensure total is processed correctly
            var processedDetail = detail
            if let totalValue = detail["total"] {
                if let totalDouble = totalValue as? Double {
                    processedDetail["total"] = totalDouble
                    print("✅ Total is already Double: \(totalDouble)")
                } else if let totalString = totalValue as? String, let totalDouble = Double(totalString) {
                    processedDetail["total"] = totalDouble
                    print("✅ Converted total from String to Double: \(totalDouble)")
                } else if let totalInt = totalValue as? Int {
                    processedDetail["total"] = Double(totalInt)
                    print("✅ Converted total from Int to Double: \(Double(totalInt))")
                }
            }
            
            self.orderDetail = processedDetail
            
            if let its = processedDetail["itens"] as? [[String: Any]] {
                print("📦 fetchOrderDetail: \(its.count) itens encontrados")
                self.itens = its
            } else {
                print("⚠️ fetchOrderDetail: Campo 'itens' não encontrado ou não é um array")
                self.itens = []
            }
            
            // Converter os campos numeros para garantir que estão como Double
            for (index, var item) in itens.enumerated() {
                if let quantidade = item["quantidade"] {
                    if let quantidadeInt = quantidade as? Int {
                        item["quantidade"] = quantidadeInt
                    } else if let quantidadeString = quantidade as? String, 
                              let quantidadeInt = Int(quantidadeString) {
                        item["quantidade"] = quantidadeInt
                    }
                }
                
                if let precoUnitario = item["preco_unitario"] {
                    if let precoDouble = precoUnitario as? Double {
                        item["preco_unitario"] = precoDouble
                    } else if let precoString = precoUnitario as? String,
                              let precoDouble = Double(precoString) {
                        item["preco_unitario"] = precoDouble
                    } else if let precoInt = precoUnitario as? Int {
                        item["preco_unitario"] = Double(precoInt)
                    }
                }
                
                if index < itens.count {
                    itens[index] = item
                }
            }
            
            isLoading = false
        } catch {
            print("❌ fetchOrderDetail: Erro: \(error)")
            errorMessage = "Erro ao carregar detalhes: \(error.localizedDescription)"
            isLoading = false
        }
    }
    
    // Helper function to format date in order details
    private func formatOrderDate(_ dateString: String) -> String? {
        print("📅 Formatting order date: \(dateString)")
        
        // Use standard MySQL date format (from DATE_FORMAT in SQL)
        let formatter = DateFormatter()
        formatter.locale = Locale(identifier: "pt_PT")
        
        // Try MySQL format first (yyyy-MM-dd HH:mm:ss)
        formatter.dateFormat = "yyyy-MM-dd HH:mm:ss"
        
        if let date = formatter.date(from: dateString) {
            formatter.dateStyle = .medium
            formatter.timeStyle = .short
            let formattedDate = formatter.string(from: date)
            print("✅ Date formatted successfully: \(formattedDate)")
            return formattedDate
        }
        
        // Try alternative formats if the first one fails
        let alternativeFormats = [
            "yyyy-MM-dd'T'HH:mm:ss",
            "yyyy-MM-dd'T'HH:mm:ss.SSS",
            "yyyy-MM-dd'T'HH:mm:ssZ",
            "yyyy-MM-dd'T'HH:mm:ss.SSSZ",
            "yyyy-MM-dd"
        ]
        
        for format in alternativeFormats {
            formatter.dateFormat = format
            if let date = formatter.date(from: dateString) {
                formatter.dateStyle = .medium
                formatter.timeStyle = .short
                let formattedDate = formatter.string(from: date)
                print("✅ Date formatted with alternative format: \(formattedDate)")
                return formattedDate
            }
        }
        
        print("❌ Failed to format date with any format")
        return nil
    }
    
    private func getTotal(from orderDetail: [String: Any]) -> Double {
        // Try multiple approaches to get the total
        if let totalDouble = orderDetail["total"] as? Double {
            print("✅ Total as Double: \(totalDouble)")
            return totalDouble
        } else if let totalString = orderDetail["total"] as? String, 
                  let totalDouble = Double(totalString) {
            print("✅ Total converted from String: \(totalDouble)")
            return totalDouble
        } else if let totalInt = orderDetail["total"] as? Int {
            let totalDouble = Double(totalInt)
            print("✅ Total converted from Int: \(totalDouble)")
            return totalDouble
        } else {
            // Calculate total from items as fallback
            let itemsTotal = itens.reduce(0) { result, item in
                let price = item["preco_unitario"] as? Double ?? 0
                let quantity = item["quantidade"] as? Int ?? 0
                return result + (price * Double(quantity))
            }
            print("⚠️ Total calculated from items: \(itemsTotal)")
            return itemsTotal
        }
    }
    
    private func canReviewOrder(_ orderDetail: [String: Any]) -> Bool {
        // Verificar se a encomenda está confirmada/paga
        if let status = orderDetail["status"] as? String {
            return status.lowercased() == "confirmado" || status.lowercased() == "pago"
        }
        return false
    }
}

extension OrderStatus {
    var color: Color {
        switch self {
        case .pending:
            return .orange
        case .paid:
            return .blue
        case .shipped:
            return .green
        case .cancelled:
            return .red
        }
    }
}

#Preview {
    OrdersView()
}
