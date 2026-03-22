/*
 ROTAS DE PAGAMENTOS - stripe.js
 
 Este ficheiro gere todos os pagamentos da aplicação usando o Stripe.
 O Stripe é um serviço seguro para processar pagamentos online.
 É como um "terminal de pagamento digital" que processa cartões de crédito.
 
 RESPONSABILIDADES:
 - Criar intenções de pagamento para o Stripe
 - Processar pagamentos de forma segura
 - Validar dados de pagamento
 - Integrar com a aplicação iOS
 - Gerir metadados dos pagamentos (utilizador, items, endereço)
*/

const express = require('express');
const router = express.Router();
const mysql = require('mysql2');
const stripe = require('stripe')(process.env.STRIPE_SECRET_KEY); // Inicializa Stripe com chave secreta

// CONFIGURAÇÃO DA BASE DE DADOS
// Cria pool de conexões para melhor performance
const db = mysql.createPool({
    host: process.env.DB_HOST,
    user: process.env.DB_USER,
    password: process.env.DB_PASSWORD,
    database: process.env.DB_NAME
}).promise(); // .promise() permite usar async/await

/**
 * ENDPOINT PARA CRIAR INTENÇÃO DE PAGAMENTO
 * 
 * Esta rota é chamada pela aplicação iOS quando o utilizador quer fazer uma compra.
 * Cria uma "intenção de pagamento" no Stripe, que é como uma "reserva" do pagamento.
 * 
 * Fluxo:
 * 1. App iOS envia dados da compra
 * 2. Servidor valida os dados
 * 3. Stripe cria PaymentIntent
 * 4. Servidor retorna "client secret" para a app
 * 5. App iOS usa este "secret" para processar o pagamento
 */
router.post('/create-payment-intent', async (req, res) => {
    try {
        // Extrai dados enviados pela aplicação iOS
        const { amount, currency = 'eur', userId, items, shippingAddress } = req.body;

        // Validação básica: verifica se todos os dados necessários foram enviados
        if (!amount || !userId || !items || items.length === 0 || !shippingAddress) {
            return res.status(400).json({
                error: 'Dados incompletos. Necessário: amount, userId, items e shippingAddress'
            });
        }

        console.log(`[Stripe] Criando intenção de pagamento para usuário ${userId}, valor: ${amount} ${currency}`);

        // Cria o PaymentIntent no Stripe
        const paymentIntent = await stripe.paymentIntents.create({
            amount: Math.round(amount * 100), // Stripe trabalha em centavos (€1 = 100 centavos)
            currency,                         // Moeda (EUR, USD, etc.)
            automatic_payment_methods: {
                enabled: true,               // Permite vários métodos de pagamento automaticamente
            },
            metadata: {                      // Dados extra associados ao pagamento
                userId: userId,
                itemCount: items.length,
                shippingAddress: shippingAddress
            }
        });

        console.log(`[Stripe] PaymentIntent criado com ID: ${paymentIntent.id}`);

        // Retorna o "client secret" que a app iOS precisa para processar o pagamento
        res.json({
            clientSecret: paymentIntent.client_secret, // Chave secreta para a app
            paymentIntentId: paymentIntent.id          // ID do pagamento para referência
        });

    } catch (error) {
        console.error('[Stripe] Erro ao criar PaymentIntent:', error);
        res.status(500).json({
            error: 'Erro ao processar pagamento',
            message: error.message
        });
    }
});

/**
 * Endpoint para verificar o status de um pagamento
 */
router.get('/payment-status/:paymentIntentId', async (req, res) => {
    try {
        const { paymentIntentId } = req.params;

        if (!paymentIntentId) {
            return res.status(400).json({ error: 'ID do PaymentIntent é necessário' });
        }

        const paymentIntent = await stripe.paymentIntents.retrieve(paymentIntentId);

        res.json({
            id: paymentIntent.id,
            status: paymentIntent.status,
            amount: paymentIntent.amount / 100, // Converte de centavos para a moeda principal
            currency: paymentIntent.currency
        });

    } catch (error) {
        console.error('[Stripe] Erro ao verificar status do pagamento:', error);
        res.status(500).json({
            error: 'Erro ao verificar status do pagamento',
            message: error.message
        });
    }
});

module.exports = router;