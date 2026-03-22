#!/bin/bash

# Script para corrigir configurações do Xcode que causam problemas com CocoaPods
echo "Corrigindo configurações do projeto Xcode..."

PROJECT_FILE="/Users/pedrofangueirosilva/Desktop/ios appP/boxingforlife/boxingforlife.xcodeproj/project.pbxproj"

# Backup do arquivo original
cp "$PROJECT_FILE" "${PROJECT_FILE}.backup"

# Ajusta os LD_RUNPATH_SEARCH_PATHS para usar $(inherited)
sed -i '' 's/LD_RUNPATH_SEARCH_PATHS = \([^;]*\);/LD_RUNPATH_SEARCH_PATHS = ($(inherited), \1);/g' "$PROJECT_FILE"

echo "Configurações corrigidas! Um backup foi criado em ${PROJECT_FILE}.backup"
echo "Agora, feche o Xcode (se estiver aberto) e reabra usando 'boxingforlife.xcworkspace'"