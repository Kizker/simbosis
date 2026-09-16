/**
 * Format date string into Indonesian readable format (e.g., "14 Mei 2026")
 * @param {string} dateStr ISO timestamp or date string
 * @returns {string} Formatted date or fallback to original string
 */
export function formatIndonesianDate(dateStr) {
  if (!dateStr) return '';
  if (dateStr.includes('T') || /^\d{4}-\d{2}-\d{2}/.test(dateStr)) {
    try {
      const date = new Date(dateStr);
      if (isNaN(date.getTime())) return dateStr;
      
      const months = [
        'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
        'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
      ];
      
      const day = date.getDate();
      const month = months[date.getMonth()];
      const year = date.getFullYear();
      
      return `${day} ${month} ${year}`;
    } catch (e) {
      return dateStr;
    }
  }
  return dateStr;
}
