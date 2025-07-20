import { createRouter, createWebHistory } from 'vue-router'
import OrdersList from './views/OrdersList.vue'
import OrderCreate from './views/OrderCreate.vue'
import OrderEdit from './views/OrderEdit.vue'
import ProductsList from './views/ProductsList.vue'
import WarehousesList from './views/WarehousesList.vue'
import OrderMovement from './views/OrderMovement.vue'

const routes = [
    { path: '/orders', component: OrdersList },
    { path: '/orders/create', component: OrderCreate },
    { path: '/orders/:id/edit', component: OrderEdit, props: true },
    { path: '/products', component: ProductsList },
    { path: '/warehouses', component: WarehousesList },
    { path: '/movements', component: OrderMovement },
]

const router = createRouter({
    history: createWebHistory(),
    routes,
})

export default router
