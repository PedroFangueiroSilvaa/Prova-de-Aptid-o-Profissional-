import SwiftUI

struct SubmitProductReviewView: View {
    let codigoBase: String
    let idEncomenda: Int
    let idUtilizador: Int
    
    @State private var classificacao: Int = 5
    @State private var comentario: String = ""
    @State private var isSubmitting = false
    @State private var showSuccess = false
    @State private var showError = false
    @State private var errorMessage = ""
    
    @Environment(\.dismiss) private var dismiss
    
    var body: some View {
        NavigationView {
            VStack(spacing: 24) {
                // Título
                VStack(spacing: 8) {
                    Text("Avaliar Produto")
                        .font(.title2)
                        .bold()
                    
                    Text("Partilhe a sua experiência com este produto")
                        .font(.caption)
                        .foregroundColor(.gray)
                        .multilineTextAlignment(.center)
                }
                
                // Seleção de classificação
                VStack(spacing: 16) {
                    Text("Classificação")
                        .font(.headline)
                    
                    HStack(spacing: 8) {
                        ForEach(1...5, id: \.self) { star in
                            Button {
                                classificacao = star
                            } label: {
                                Image(systemName: star <= classificacao ? "star.fill" : "star")
                                    .font(.system(size: 32))
                                    .foregroundColor(.yellow)
                            }
                        }
                    }
                    
                    Text("\(classificacao) estrela\(classificacao == 1 ? "" : "s")")
                        .font(.caption)
                        .foregroundColor(.gray)
                }
                
                // Campo de comentário
                VStack(alignment: .leading, spacing: 8) {
                    Text("Comentário (opcional)")
                        .font(.headline)
                    
                    TextField("Escreva aqui a sua opinião sobre o produto...", text: $comentario, axis: .vertical)
                        .textFieldStyle(RoundedBorderTextFieldStyle())
                        .lineLimit(3...6)
                }
                
                Spacer()
                
                // Botão de submissão
                Button {
                    Task {
                        await submitReview()
                    }
                } label: {
                    HStack {
                        if isSubmitting {
                            ProgressView()
                                .scaleEffect(0.8)
                        }
                        Text(isSubmitting ? "Enviando..." : "Enviar Avaliação")
                    }
                    .frame(maxWidth: .infinity)
                    .padding()
                    .background(Color.blue)
                    .foregroundColor(.white)
                    .cornerRadius(12)
                }
                .disabled(isSubmitting)
            }
            .padding()
            .navigationTitle("Nova Avaliação")
            .navigationBarTitleDisplayMode(.inline)
            .toolbar {
                ToolbarItem(placement: .navigationBarLeading) {
                    Button("Cancelar") {
                        dismiss()
                    }
                }
            }
        }
        .alert("Sucesso", isPresented: $showSuccess) {
            Button("OK") {
                dismiss()
            }
        } message: {
            Text("Avaliação enviada com sucesso!")
        }
        .alert("Erro", isPresented: $showError) {
            Button("OK", role: .cancel) {}
        } message: {
            Text(errorMessage)
        }
    }
    
    private func submitReview() async {
        isSubmitting = true
        
        do {
            try await APIService.shared.submitProductReview(
                idEncomenda: idEncomenda,
                codigoBase: codigoBase,
                idUtilizador: idUtilizador,
                classificacao: classificacao,
                comentario: comentario
            )
            showSuccess = true
        } catch {
            errorMessage = "Erro ao enviar avaliação: \(error.localizedDescription)"
            showError = true
        }
        
        isSubmitting = false
    }
}

#Preview {
    SubmitProductReviewView(
        codigoBase: "1001-9002-3001",
        idEncomenda: 1,
        idUtilizador: 1
    )
}
