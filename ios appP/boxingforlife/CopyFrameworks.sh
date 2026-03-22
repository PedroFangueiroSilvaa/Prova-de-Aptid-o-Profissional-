#!/bin/sh
set -e

echo "Copiando frameworks..."

# Verificar se as variáveis de ambiente estão definidas
if [ -z "$BUILT_PRODUCTS_DIR" ] || [ -z "$FRAMEWORKS_FOLDER_PATH" ]; then
  echo "ERRO: Variáveis de ambiente necessárias não estão definidas"
  echo "BUILT_PRODUCTS_DIR: $BUILT_PRODUCTS_DIR"
  echo "FRAMEWORKS_FOLDER_PATH: $FRAMEWORKS_FOLDER_PATH"
  exit 1
fi

# Criar o diretório de destino
FRAMEWORKS_DIR="${BUILT_PRODUCTS_DIR}/${FRAMEWORKS_FOLDER_PATH}"
echo "Criando diretório de destino: $FRAMEWORKS_DIR"
mkdir -p "$FRAMEWORKS_DIR"

# Verificar se o PODS_ROOT está definido
if [ -z "$PODS_ROOT" ]; then
  echo "ERRO: PODS_ROOT não está definido"
  # Tentar determinar o caminho do PODS_ROOT
  PODS_ROOT=$(cd "${SRCROOT}/Pods" 2>/dev/null && pwd)
  if [ -z "$PODS_ROOT" ]; then
    echo "Não foi possível determinar o caminho do PODS_ROOT"
    exit 1
  else
    echo "PODS_ROOT determinado automaticamente: $PODS_ROOT"
  fi
fi

# Lista de frameworks que precisam ser copiados
FRAMEWORKS=(
  "${PODS_ROOT}/StripeApplePay/StripeApplePay.framework" 
  "${PODS_ROOT}/StripeCore/StripeCore.framework"
  "${PODS_ROOT}/StripePayments/StripePayments.framework"
  "${PODS_ROOT}/StripePaymentSheet/StripePaymentSheet.framework"
  "${PODS_ROOT}/StripePaymentsUI/StripePaymentsUI.framework"
  "${PODS_ROOT}/StripeUICore/StripeUICore.framework"
)

# Copiar os frameworks
for framework in "${FRAMEWORKS[@]}"; do
  if [ -d "$framework" ]; then
    echo "Copiando $framework para $FRAMEWORKS_DIR/"
    ditto "$framework" "$FRAMEWORKS_DIR/$(basename "$framework")"
  else
    echo "Framework não encontrado: $framework"
  fi
done

echo "Frameworks copiados com sucesso!"