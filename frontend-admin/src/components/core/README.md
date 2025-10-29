# Core Components

This directory contains reusable Vue components that can be used throughout the entire project. All components follow consistent design patterns and are built with accessibility and responsiveness in mind.

## Components

### 1. Button Component

A versatile button component with multiple variants, sizes, and states.

```vue
<template>
  <div>
    <!-- Basic usage -->
    <Button variant="primary">Primary Button</Button>
    
    <!-- With icon -->
    <Button variant="success" icon="save">Save</Button>
    
    <!-- Loading state -->
    <Button variant="primary" :loading="true">Loading...</Button>
    
    <!-- Different sizes -->
    <Button size="sm" variant="outline-primary">Small</Button>
    <Button size="md" variant="primary">Medium</Button>
    <Button size="lg" variant="primary">Large</Button>
    
    <!-- Block button -->
    <Button variant="primary" block>Full Width</Button>
  </div>
</template>

<script setup>
import { Button } from '@/components/core'
</script>
```

**Props:**
- `variant`: 'primary' | 'secondary' | 'success' | 'danger' | 'warning' | 'info' | 'light' | 'dark' | 'outline-primary' | etc.
- `size`: 'sm' | 'md' | 'lg'
- `type`: 'button' | 'submit' | 'reset'
- `disabled`: Boolean
- `loading`: Boolean
- `icon`: String (icon name)
- `block`: Boolean (full width)

### 2. Modal Component

A flexible modal dialog component with customizable header, body, and footer.

```vue
<template>
  <div>
    <Button @click="showModal = true">Open Modal</Button>
    
    <Modal
      :show="showModal"
      title="User Details"
      size="md"
      :loading="saving"
      @close="showModal = false"
      @confirm="handleSave"
    >
      <div class="p-4">
        <CustomInput v-model="form.name" label="Name" />
        <CustomInput v-model="form.email" label="Email" type="email" />
      </div>
      
      <template #footer>
        <Button variant="secondary" @click="showModal = false">Cancel</Button>
        <Button variant="primary" @click="handleSave" :loading="saving">Save</Button>
      </template>
    </Modal>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { Modal, Button, CustomInput } from '@/components/core'

const showModal = ref(false)
const saving = ref(false)
const form = ref({ name: '', email: '' })

function handleSave() {
  saving.value = true
  // Save logic here
  setTimeout(() => {
    saving.value = false
    showModal.value = false
  }, 1000)
}
</script>
```

**Props:**
- `show`: Boolean
- `title`: String
- `size`: 'sm' | 'md' | 'lg' | 'xl' | 'full'
- `showHeader`: Boolean (default: true)
- `showFooter`: Boolean (default: true)
- `cancelText`: String (default: 'Cancel')
- `confirmText`: String (default: 'Save')
- `loading`: Boolean
- `closeOnOverlay`: Boolean (default: true)

### 3. Table Component

A comprehensive data table with sorting, pagination, search, and customizable columns.

```vue
<template>
  <Table
    :data="users"
    :columns="columns"
    :loading="loading"
    title="Users"
    searchable
    :paginated="true"
    :page-size="10"
    @edit="handleEdit"
    @delete="handleDelete"
    @search="handleSearch"
  >
    <!-- Custom cell content -->
    <template #cell-status="{ value }">
      <span :class="getStatusClass(value)">{{ value }}</span>
    </template>
    
    <!-- Custom actions -->
    <template #actions="{ row }">
      <Button size="sm" variant="outline-primary" icon="edit" @click="handleEdit(row)" />
      <Button size="sm" variant="outline-danger" icon="delete" @click="handleDelete(row)" />
    </template>
  </Table>
</template>

<script setup>
import { ref } from 'vue'
import { Table, Button } from '@/components/core'

const loading = ref(false)
const users = ref([
  { id: 1, name: 'John Doe', email: 'john@example.com', status: 'active' },
  { id: 2, name: 'Jane Smith', email: 'jane@example.com', status: 'inactive' }
])

const columns = [
  { key: 'name', label: 'Name', sortable: true },
  { key: 'email', label: 'Email', sortable: true },
  { key: 'status', label: 'Status', sortable: true, align: 'center' }
]

function handleEdit(user) {
  console.log('Edit user:', user)
}

function handleDelete(user) {
  console.log('Delete user:', user)
}

function handleSearch(query) {
  console.log('Search:', query)
}

function getStatusClass(status) {
  return status === 'active' ? 'text-green-600' : 'text-red-600'
}
</script>
```

**Props:**
- `data`: Array of objects
- `columns`: Array of column definitions
- `loading`: Boolean
- `title`: String
- `searchable`: Boolean
- `searchPlaceholder`: String
- `showHeader`: Boolean (default: true)
- `showRefresh`: Boolean (default: true)
- `emptyText`: String
- `paginated`: Boolean (default: true)
- `pageSize`: Number (default: 10)
- `rowKey`: String (default: 'id')

### 4. Form Component

A form wrapper with consistent styling and validation support.

```vue
<template>
  <Form
    :loading="saving"
    submit-text="Create User"
    cancel-text="Cancel"
    layout="vertical"
    @submit="handleSubmit"
    @cancel="handleCancel"
  >
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
    
    <FormGroup label="Active" layout="inline">
      <CustomInput v-model="form.active" name="active" type="checkbox" />
    </FormGroup>
  </Form>
</template>

<script setup>
import { ref, reactive } from 'vue'
import { Form, FormGroup, CustomInput } from '@/components/core'

const saving = ref(false)
const form = reactive({
  name: '',
  email: '',
  role: 'user',
  active: true
})

const errors = ref({
  name: [],
  email: [],
  role: []
})

const roleOptions = [
  { key: 'admin', text: 'Administrator' },
  { key: 'user', text: 'User' },
  { key: 'guest', text: 'Guest' }
]

function handleSubmit() {
  saving.value = true
  // Submit logic here
  setTimeout(() => {
    saving.value = false
  }, 1000)
}

function handleCancel() {
  // Reset form or navigate away
}
</script>
```

**Props:**
- `loading`: Boolean
- `showActions`: Boolean (default: true)
- `submitText`: String (default: 'Save')
- `cancelText`: String (default: 'Cancel')
- `layout`: 'vertical' | 'horizontal' | 'inline'
- `size`: 'sm' | 'md' | 'lg'
- `disabled`: Boolean
- `validateOnSubmit`: Boolean (default: true)

### 5. FormGroup Component

A wrapper component for form fields with consistent labeling and error handling.

```vue
<template>
  <FormGroup
    label="Email Address"
    name="email"
    required
    help-text="We'll never share your email with anyone else"
    :errors="errors.email"
  >
    <CustomInput
      v-model="email"
      type="email"
      placeholder="Enter your email"
      required
    />
  </FormGroup>
</template>

<script setup>
import { ref } from 'vue'
import { FormGroup, CustomInput } from '@/components/core'

const email = ref('')
const errors = ref({
  email: ['Email is required']
})
</script>
```

**Props:**
- `label`: String
- `name`: String
- `required`: Boolean
- `disabled`: Boolean
- `helpText`: String
- `errors`: Array of error messages
- `size`: 'sm' | 'md' | 'lg'
- `layout`: 'vertical' | 'horizontal' | 'inline'

### 6. CustomInput Component

An enhanced input component supporting various input types including text, email, select, textarea, rich text, and file uploads.

```vue
<template>
  <div>
    <!-- Text input -->
    <CustomInput v-model="name" label="Name" required />
    
    <!-- Email input -->
    <CustomInput v-model="email" label="Email" type="email" />
    
    <!-- Password input -->
    <CustomInput v-model="password" label="Password" type="password" />
    
    <!-- Select dropdown -->
    <CustomInput
      v-model="country"
      label="Country"
      type="select"
      :select-options="countries"
    />
    
    <!-- Textarea -->
    <CustomInput v-model="description" label="Description" type="textarea" />
    
    <!-- Rich text editor -->
    <CustomInput
      v-model="content"
      label="Content"
      type="richtext"
      :editor-config="editorConfig"
    />
    
    <!-- File upload -->
    <CustomInput v-model="file" label="Upload File" type="file" />
    
    <!-- Checkbox -->
    <CustomInput v-model="agreed" label="I agree to terms" type="checkbox" />
    
    <!-- With prepend/append -->
    <CustomInput
      v-model="price"
      label="Price"
      type="number"
      prepend="$"
      append=".00"
    />
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { CustomInput } from '@/components/core'

const name = ref('')
const email = ref('')
const password = ref('')
const country = ref('')
const description = ref('')
const content = ref('')
const file = ref(null)
const agreed = ref(false)
const price = ref(0)

const countries = [
  { key: 'us', text: 'United States' },
  { key: 'uk', text: 'United Kingdom' },
  { key: 'ca', text: 'Canada' }
]

const editorConfig = {
  toolbar: ['bold', 'italic', 'link', 'bulletedList', 'numberedList']
}
</script>
```

**Props:**
- `modelValue`: String | Number | File
- `label`: String
- `type`: 'text' | 'email' | 'password' | 'number' | 'select' | 'textarea' | 'richtext' | 'file' | 'checkbox'
- `name`: String
- `required`: Boolean
- `prepend`: String
- `append`: String
- `selectOptions`: Array of { key, text } objects
- `errors`: Array of error messages
- `editorConfig`: Object (for rich text editor)

## Usage in Project

### Import Components

```javascript
// Import individual components
import { Button, Modal, Table, Form, FormGroup, CustomInput } from '@/components/core'

// Or import all at once
import * as CoreComponents from '@/components/core'
```

### Global Registration (Optional)

```javascript
// In main.js or app.js
import * as CoreComponents from '@/components/core'

Object.entries(CoreComponents).forEach(([name, component]) => {
  app.component(name, component)
})
```

## Best Practices

1. **Consistent Styling**: All components use Tailwind CSS classes for consistent styling
2. **Accessibility**: Components include proper ARIA labels and keyboard navigation
3. **Responsive Design**: Components adapt to different screen sizes
4. **Error Handling**: Built-in error display and validation support
5. **Loading States**: Support for loading indicators and disabled states
6. **TypeScript Support**: Components are written with TypeScript in mind

## Customization

Components can be customized through:
- Props for different variants and behaviors
- CSS classes for styling overrides
- Slots for custom content
- Events for interaction handling

## Examples

See the `examples/` directory for complete working examples of each component in various scenarios.
