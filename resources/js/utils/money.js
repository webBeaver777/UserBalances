export function formatAmount(amount, type) {
  const prefix = type === 'deposit' ? '+' : '-';
  const num = Number(amount) || 0;
  return `${prefix}${new Intl.NumberFormat('ru-RU', {
    style: 'currency',
    currency: 'RUB'
  }).format(num)}`;
}

