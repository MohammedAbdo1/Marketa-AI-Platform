<template>
  <div class="p-6 max-w-4xl mx-auto">
    <h1 class="text-3xl font-bold mb-8">Core Components Examples</h1>
    
    <!-- Button Examples -->
    <section class="mb-12">
      <h2 class="text-2xl font-semibold mb-4">Buttons</h2>
      <div class="flex flex-wrap gap-4">
        <Button variant="primary">Primary</Button>
        <Button variant="secondary">Secondary</Button>
        <Button variant="success">Success</Button>
        <Button variant="danger">Danger</Button>
        <Button variant="warning">Warning</Button>
        <Button variant="info">Info</Button>
        <Button variant="outline-primary">Outline Primary</Button>
        <Button variant="outline-danger">Outline Danger</Button>
      </div>
      
      <div class="mt-4">
        <h3 class="text-lg font-medium mb-2">With Icons</h3>
        <div class="flex flex-wrap gap-4">
          <Button variant="primary" icon="save">Save</Button>
          <Button variant="success" icon="plus">Add New</Button>
          <Button variant="danger" icon="delete">Delete</Button>
          <Button variant="secondary" icon="edit">Edit</Button>
        </div>
      </div>
      
      <div class="mt-4">
        <h3 class="text-lg font-medium mb-2">Loading States</h3>
        <div class="flex flex-wrap gap-4">
          <Button variant="primary" :loading="true">Loading...</Button>
          <Button variant="success" :loading="loading" @click="toggleLoading">
            Toggle Loading
          </Button>
        </div>
      </div>
    </section>
    
    <!-- Form Examples -->
    <section class="mb-12">
      <h2 class="text-2xl font-semibold mb-4">Forms</h2>
      <Form @submit="handleSubmit" @cancel="handleCancel">
        <FormGroup label="Name" required :errors="errors.name">
          <CustomInput v-model="form.name" name="name" required />
        </FormGroup>
        
        <FormGroup label="Email" required :errors="errors.email">
          <CustomInput v-model="form.email" name="email" type="email" required />
        </FormGroup>
        
        <FormGroup label="Role" :errors="errors.role">
          <CustomInput
            v-model="form.role"
            name="role"
            type="select"
            :select-options="roleOptions"
          />
        </FormGroup>
        
        <FormGroup label="Bio" :errors="errors.bio">
          <CustomInput v-model="form.bio" name="bio" type="textarea" />
        </FormGroup>
        
        <FormGroup label="Active" layout="inline">
          <CustomInput v-model="form.active" name="active" type="checkbox" />
        </FormGroup>
      </Form>
    </section>
    
    <!-- Modal Example -->
    <section class="mb-12">
      <h2 class="text-2xl font-semibold mb-4">Modal</h2>
      <Button variant="primary" @click="showModal = true">Open Modal</Button>
      
      <Modal
        :show="showModal"
        title="Example Modal"
        size="md"
        :loading="modalLoading"
        @close="showModal = false"
        @confirm="handleModalConfirm"
      >
        <div class="space-y-4">
          <p>This is an example modal content. You can put any content here.</p>
          <CustomInput v-model="modalForm.message" label="Message" type="textarea" />
        </div>
      </Modal>
    </section>
    
    <!-- Table Example -->
    <section class="mb-12">
      <h2 class="text-2xl font-semibold mb-4">Table</h2>
      <Table
        :data="tableData"
        :columns="tableColumns"
        :loading="tableLoading"
        title="Sample Data"
        searchable
        :paginated="true"
        :page-size="5"
        @edit="handleEdit"
        @delete="handleDelete"
        @search="handleSearch"
      >
        <template #cell-status="{ value }">
          <span :class="getStatusClass(value)">{{ value }}</span>
        </template>
      </Table>
    </section>
  </div>
</template>

<script setup>
import { ref, reactive } from 'vue'
import { Button, Modal, Form, FormGroup, CustomInput, Table } from '@/components/core'

// Button examples
const loading = ref(false)

function toggleLoading() {
  loading.value = true
  setTimeout(() => {
    loading.value = false
  }, 2000)
}

// Form examples
const form = reactive({
  name: '',
  email: '',
  role: 'user',
  bio: '',
  active: true
})

const errors = ref({
  name: [],
  email: [],
  role: [],
  bio: []
})

const roleOptions = [
  { key: 'admin', text: 'Administrator' },
  { key: 'user', text: 'User' },
  { key: 'guest', text: 'Guest' }
]

function handleSubmit() {
  console.log('Form submitted:', form)
}

function handleCancel() {
  console.log('Form cancelled')
}

// Modal examples
const showModal = ref(false)
const modalLoading = ref(false)
const modalForm = reactive({
  message: ''
})

function handleModalConfirm() {
  modalLoading.value = true
  setTimeout(() => {
    modalLoading.value = false
    showModal.value = false
    console.log('Modal confirmed:', modalForm)
  }, 1000)
}

// Table examples
const tableLoading = ref(false)
const tableData = ref([
  { id: 1, name: 'John Doe', email: 'john@example.com', status: 'active', role: 'admin' },
  { id: 2, name: 'Jane Smith', email: 'jane@example.com', status: 'inactive', role: 'user' },
  { id: 3, name: 'Bob Johnson', email: 'bob@example.com', status: 'active', role: 'user' },
  { id: 4, name: 'Alice Brown', email: 'alice@example.com', status: 'pending', role: 'guest' },
  { id: 5, name: 'Charlie Wilson', email: 'charlie@example.com', status: 'active', role: 'user' }
])

const tableColumns = [
  { key: 'name', label: 'Name', sortable: true },
  { key: 'email', label: 'Email', sortable: true },
  { key: 'status', label: 'Status', sortable: true, align: 'center' },
  { key: 'role', label: 'Role', sortable: true }
]

function handleEdit(row) {
  console.log('Edit row:', row)
}

function handleDelete(row) {
  console.log('Delete row:', row)
}

function handleSearch(query) {
  console.log('Search:', query)
}

function getStatusClass(status) {
  switch (status) {
    case 'active':
      return 'text-green-600 font-medium'
    case 'inactive':
      return 'text-red-600 font-medium'
    case 'pending':
      return 'text-yellow-600 font-medium'
    default:
      return 'text-gray-600'
  }
}
</script>
