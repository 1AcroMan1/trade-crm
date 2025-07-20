<template>
  <div class="app-container">
    <div class="main-content">
      <div class="card">
        <h1 class="page-title">История движений товаров</h1>
        
        <div v-if="loading" class="p-4 text-center">Загрузка данных...</div>
        <div v-else-if="error" class="p-4 text-red-500">{{ error }}</div>
        
        <template v-else>
          <div class="overflow-x-auto">
            <table class="table-container">
              <thead class="table-header">
                <tr>
                  <th class="table-header-cell">Заказ</th>
                  <th class="table-header-cell">Клиент</th>
                  <th class="table-header-cell">Склад</th>
                  <th class="table-header-cell">Дата завершения</th>
                  <th class="table-header-cell">Товары</th>
                  <th class="table-header-cell">Сумма</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="movement in movements" :key="movement.order_id" class="table-row">
                  <td class="table-cell">#{{ movement.order_id }}</td>
                  <td class="table-cell">{{ movement.customer }}</td>
                  <td class="table-cell">{{ movement.warehouse }}</td>
                  <td class="table-cell">{{ formatDate(movement.completed_at) }}</td>
                  <td class="table-cell">
                    <ul class="list-disc pl-5">
                      <li v-for="(item, index) in movement.items" :key="index">
                        {{ item.product_name }} ({{ item.quantity }} × {{ item.price }} ₽)
                      </li>
                    </ul>
                  </td>
                  <td class="table-cell">
                    {{ calculateTotal(movement.items) }} ₽
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <div class="pagination">
            <button 
              @click="fetchMovements(meta.current_page - 1)" 
              :disabled="meta.current_page === 1"
              class="pagination-btn"
            >
              Назад
            </button>
            <span>Страница {{ meta.current_page }} из {{ meta.last_page }}</span>
            <button 
              @click="fetchMovements(meta.current_page + 1)" 
              :disabled="meta.current_page === meta.last_page"
              class="pagination-btn"
            >
              Вперед
            </button>
          </div>
        </template>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'

const movements = ref([])
const loading = ref(true)
const error = ref(null)
const meta = ref({
  current_page: 1,
  last_page: 1,
  total: 0,
  per_page: 15
})

const fetchMovements = async (page = 1) => {
  try {
    loading.value = true
    error.value = null
    
    const response = await axios.get('/api/movement', {
      params: { page }
    })
    
    movements.value = response.data.data
    meta.value = response.data.meta
    
  } catch (err) {
    console.error('Error fetching movements:', err)
    error.value = err.response?.data?.message || err.message
  } finally {
    loading.value = false
  }
}

const formatDate = (dateString) => {
  return new Date(dateString).toLocaleString()
}

const calculateTotal = (items) => {
  return items.reduce((sum, item) => sum + item.total, 0)
}

onMounted(() => {
  fetchMovements()
})
</script>

<style scoped>
/* Дополнительные стили для улучшения внешнего вида */
.list-disc li {
  margin-bottom: 0.25rem;
}

.table-cell {
  white-space: normal;
  vertical-align: top;
}

.table-cell ul {
  margin-top: 0.5rem;
  margin-bottom: 0.5rem;
}

.pagination {
  padding: 1rem;
  border-top: 1px solid #e5e7eb;
  margin-top: 1rem;
}

.pagination span {
  font-size: 0.875rem;
  color: #4b5563;
}
</style>
