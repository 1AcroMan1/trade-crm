<template>
  <div class="card">
    <h1 class="page-title">Список складов</h1>

    <table class="table-container">
      <thead class="table-header">
        <tr>
          <th class="table-header-cell">ID</th>
          <th class="table-header-cell">Название</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="warehouse in warehouses" :key="warehouse.id" class="table-row">
          <td class="table-cell">{{ warehouse.id }}</td>
          <td class="table-cell">{{ warehouse.name }}</td>
        </tr>
      </tbody>
    </table>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'

const warehouses = ref([])

const fetchWarehouses = async () => {
  const response = await axios.get('/api/warehouses')
  warehouses.value = response.data.data
}

onMounted(() => {
  fetchWarehouses()
})
</script>
