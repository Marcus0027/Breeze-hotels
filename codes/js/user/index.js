// Taxas de fallback
const FALLBACK_RATES = {
    'USD': 0.19,   // 1 BRL = 0.19 USD
    'EUR': 0.17,
    'GBP': 0.15,
    'JPY': 25.50,
    'AUD': 0.29,
    'CAD': 0.25,
    'CHF': 0.17,
    'MXN': 3.33,
    'ARS': 270.27,
    'COP': 4761.90,
    'CLP': 909.09,
    'CNY': 1.35,
    'INR': 15.87,
    'KRW': 253.16,
    'TRY': 29.41,
    'ZAR': 3.57,
    'RUB': 90.91,
    'AED': 0.70,
    'SAR': 0.71,
    'THB': 6.67,
    'IDR': 2900,
    'VND': 4600,
    'MYR': 0.89,
    'HKD': 1.49,
    'NZD': 0.31,
    'SEK': 2.00,
    'NOK': 2.04,
    'DKK': 1.30,
    'PLN': 0.79,
    'HUF': 68.97,
    'CZK': 4.35,
    'ILS': 0.71,
    'EGP': 5.88,
    'NGN': 150,
    'PHP': 10.64
};

async function convertCurrency(currencyCode) {
    try {
        // 1. Tentar API Frankfurter primeiro (para moedas principais)
        const apiResponse = await fetch(`https://api.frankfurter.app/latest?from=BRL&to=${currencyCode}`);
        
        if (apiResponse.ok) {
            const data = await apiResponse.json();
            if (data.rates && data.rates[currencyCode]) {
                return {
                    rate: data.rates[currencyCode],
                    lastUpdate: new Date().toISOString(),
                    source: 'api'
                };
            }
        }
        
        // 2. Fallback para taxas locais se API falhar
        if (FALLBACK_RATES[currencyCode]) {
            console.warn(`Usando taxa fallback para ${currencyCode}`);
            return {
                rate: FALLBACK_RATES[currencyCode],
                lastUpdate: '2023-11-01', // Data da última atualização manual
                source: 'fallback'
            };
        }
        
        throw new Error('Moeda não encontrada');
        
    } catch (error) {
        console.error('Erro na conversão:', error);
        // 3. Fallback extremo (USD)
        return {
            rate: FALLBACK_RATES['USD'] || 0.19,
            lastUpdate: '1970-01-01',
            source: 'emergency'
        };
    }
}

// Função completa para seleção de moeda
async function handleCurrencySelection(event) {
    const currencyItem = event.currentTarget;
    const currencyCode = currencyItem.dataset.currency;
    const currencySymbol = currencyItem.dataset.symbol;
    
    // Elementos da UI
    const currencyDisplay = document.querySelector('.currency-display');
    const loadingElement = '<span class="spinner-border spinner-border-sm"></span>';
    
    try {
        // Mostrar loading
        currencyDisplay.innerHTML = loadingElement;
        
        // Obter conversão
        const conversion = await convertCurrency(currencyCode);
        
        // Atualizar sessão no servidor
        const response = await fetch('conversao.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                currency: currencyCode,
                symbol: currencySymbol,
                rate: conversion.rate,
                source: conversion.source
            })
        });
        
        if (!response.ok) throw new Error('Erro ao salvar no servidor');
        
        // Recarregar a página para aplicar mudanças
        location.reload();
        
    } catch (error) {
        console.error('Falha na conversão:', error);
        currencyDisplay.textContent = 'BRL (R$)'; // Reset para padrão
        alert(`Erro: ${error.message}\nUsando valor padrão.`);
    }
}

// Inicializar eventos
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.currency-item').forEach(item => {
        item.addEventListener('click', handleCurrencySelection);
    });
});

// Script para ocultar o URL no status bar ao passar o mouse sobre links
document.addEventListener('DOMContentLoaded', function() {
    // Seleciona todos os elementos com links que não são vazios
    const allLinks = document.querySelectorAll('a[href]:not([href^="javascript:"])');
    
    allLinks.forEach(link => {
        // Armazena o href original como dataset
        if (!link.dataset.originalHref) {
            link.dataset.originalHref = link.href;
        }

        // Remove o href quando o mouse entra (previne status bar)
        link.addEventListener('mouseenter', function() {
            this.removeAttribute('href');
        });

        // Restaura o href quando o mouse sai
        link.addEventListener('mouseleave', function() {
            if (this.dataset.originalHref) {
                this.href = this.dataset.originalHref;
            }
        });

        // Garante que o clique funcione corretamente
        link.addEventListener('click', function(e) {
            if (!this.hasAttribute('href') && this.dataset.originalHref) {
                e.preventDefault();
                
                // Trata diferentes tipos de links
                if (this.dataset.originalHref.startsWith('#')) {
                    // Scroll suave para âncoras
                    document.querySelector(this.dataset.originalHref)?.scrollIntoView({ 
                        behavior: 'smooth' 
                    });
                } else if (this.target === '_blank') {
                    // Abre em nova aba se necessário
                    window.open(this.dataset.originalHref, '_blank');
                } else {
                    // Navegação normal
                    window.location.href = this.dataset.originalHref;
                }
            }
            // Se já tem href, o comportamento padrão é mantido
        });

        // Garante que o href seja restaurado se o elemento perder foco
        link.addEventListener('blur', function() {
            if (!this.hasAttribute('href') && this.dataset.originalHref) {
                this.href = this.dataset.originalHref;
            }
        });
    });
});