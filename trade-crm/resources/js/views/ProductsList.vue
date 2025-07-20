<template>
  <div class="card">
    <h1 class="page-title">Список товаров</h1>

    <table class="table-container">
      <thead class="table-header">
        <tr>
          <th class="table-header-cell">ID</th>
          <th class="table-header-cell">Название</th>
          <th class="table-header-cell">Цена</th>
          <th class="table-header-cell">Остатки</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="product in products" :key="product.id" class="table-row">
          <td class="table-cell">{{ product.id }}</td>
          <td class="table-cell">{{ product.name }}</td>
          <td class="table-cell">{{ product.price }} ₽</td>
          <td class="table-cell">
            <div v-for="stock in product.stocks" :key="stock.warehouse_id">
              {{ getWarehouseName(stock.warehouse_id) }}: {{ stock.stock }}
            </div>
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'

const products = ref([])
const warehouses = ref([])

const fetchProducts = async () => {
  const response = await axios.get('/api/products')
  products.value = response.data.data
}

const fetchWarehouses = async () => {
  const response = await axios.get('/api/warehouses')
  warehouses.value = response.data.data
}

const getWarehouseName = (id) => {
  const warehouse = warehouses.value.find(w => w.id === id)
  return warehouse ? warehouse.name : 'Неизвестный склад'
}

onMounted(() => {
  fetchProducts()
  fetchWarehouses()
})
</script>
