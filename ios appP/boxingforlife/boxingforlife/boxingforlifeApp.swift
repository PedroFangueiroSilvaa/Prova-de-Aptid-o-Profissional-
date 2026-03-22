//
//  boxingforlifeApp.swift
//  boxingforlife
//
//  Created by Pedro Fangueiro Silva on 13/03/2025.
//

/*
 FICHEIRO PRINCIPAL DA APLICAÇÃO iOS - boxingforlifeApp.swift
 
 Este é o ponto de entrada da aplicação Boxing for Life para iOS.
 É como o "interruptor principal" que liga toda a app quando o utilizador toca no ícone.
 
 RESPONSABILIDADES:
 - Inicializar a aplicação iOS
 - Configurar o tema visual (cores, aparência)
 - Definir a primeira tela que o utilizador vê (ContentView)
 - Configurar preferências globais da app (modo claro/escuro, cor principal)
*/

import SwiftUI

// @main indica que esta é a estrutura principal que inicia toda a aplicação
@main
struct boxingforlifeApp: App {
    // body define o conteúdo principal da aplicação
    var body: some Scene {
        // WindowGroup cria uma janela onde a app vai funcionar
        WindowGroup {
            // ContentView() é a primeira tela que o utilizador vê quando abre a app
            ContentView()
                // Define que a app usa sempre o modo claro (não escuro)
                .preferredColorScheme(.light)
                // Define a cor principal da app (laranja do tema Boxing for Life)
                .accentColor(AppTheme.primaryOrange)
        }
    }
}
