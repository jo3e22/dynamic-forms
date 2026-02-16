export function useFormStatus() {
  function getStatusColor(status: string) {
    const colors = {
      draft: 'bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-100',
      pending: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-100',
      open: 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-100',
      closed: 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-100',
      incomplete: 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-100',
      complete: 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-100',
    };
    return colors[status as keyof typeof colors] || colors.draft;
  }

  return {
    getStatusColor,
  };
}