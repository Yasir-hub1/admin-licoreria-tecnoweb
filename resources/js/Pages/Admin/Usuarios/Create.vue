<template>
    <AdminLayout>
        <div class="container mx-auto px-4 py-8">
            <h1 class="text-3xl font-bold mb-6">Nuevo Usuario</h1>
            <form @submit.prevent="submit" class="bg-white shadow rounded-lg p-6 max-w-md">
                <div class="mb-4"><label class="block text-gray-700 font-bold mb-2">Nombre *</label>
                    <input v-model="form.nombre" type="text" class="w-full px-3 py-2 border rounded-lg" required />
                </div>
                <div class="mb-4"><label class="block text-gray-700 font-bold mb-2">Email *</label>
                    <input v-model="form.email" type="email" class="w-full px-3 py-2 border rounded-lg" required />
                </div>
                <div class="mb-4"><label class="block text-gray-700 font-bold mb-2">Contraseña *</label>
                    <input v-model="form.password" type="password" class="w-full px-3 py-2 border rounded-lg" required />
                </div>
                <div class="mb-4"><label class="block text-gray-700 font-bold mb-2">Rol *</label>
                    <select v-model="form.id_rol" class="w-full px-3 py-2 border rounded-lg" required>
                        <option value="">Seleccione</option>
                        <option v-for="rol in roles" :key="rol.id" :value="rol.id">{{ rol.nombre }}</option>
                    </select>
                </div>
                <div class="mb-4"><label class="block text-gray-700 font-bold mb-2">Estado *</label>
                    <select v-model="form.estado" class="w-full px-3 py-2 border rounded-lg" required>
                        <option value="activo">Activo</option>
                        <option value="inactivo">Inactivo</option>
                    </select>
                </div>
                <div class="flex gap-4">
                    <button type="submit" :disabled="form.processing" class="bg-blue-500 hover:bg-blue-600 text-white px-6 py-2 rounded-lg font-medium">Guardar</button>
                    <Link href="/admin/usuarios" class="bg-gray-500 hover:bg-gray-600 text-white px-6 py-2 rounded-lg font-medium">Cancelar</Link>
                </div>
            </form>
        </div>
    </AdminLayout>
</template>
<script setup>
import { useForm, Link } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
const props = defineProps({ roles: Array });
const form = useForm({ nombre: '', email: '', password: '', id_rol: '', estado: 'activo' });
const submit = () => form.post('/admin/usuarios');
</script>

