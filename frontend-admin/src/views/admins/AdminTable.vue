<template>
  <div class="bg-white p-4 rounded-lg shadow animate-fade-in-down">
    
    <!-- Controls: البحث وعدد الصفوف -->
    <div class="row mb-2 justify-content-end">
      <div class="text-end col-md-1 justify-content-start">
        <CustomInput
          type="select"
          v-model="filters.per_page"
          @change="updatePerPage"
          :selectOptions="[
            { key: 5, text: '5' }, 
            { key: 10, text: '10' }, 
            { key: 20, text: '20' }, 
            { key: 50, text: '50' }
          ]"
          :placeholder="t('common.per_page')"
        />
      </div>

      <div class="text-end col-md-3 justify-content-end">
        <CustomInput
          v-model="filters.search"
          name="search"
          :placeholder="t('common.search')"
        />
      </div>
    </div>

    <!-- الجدول -->
    <table class="table-auto w-full">
      <thead>
        <tr>
          <th>
            <TableHeaderCell 
              @click="sortAdmins('id')" 
              field="id" 
              :sortField="filters.sort" 
              :sortDirection="filters.direction"
            >
              {{ t('common.id') }}
            </TableHeaderCell>
          </th>
          <th>
            <TableHeaderCell 
              @click="sortAdmins('name')" 
              field="name" 
              :sortField="filters.sort" 
              :sortDirection="filters.direction"
            >
              {{ t('common.name') }}
            </TableHeaderCell>
          </th>
          <th>
            <TableHeaderCell 
              @click="sortAdmins('email')" 
              field="email" 
              :sortField="filters.sort" 
              :sortDirection="filters.direction"
            >
              {{ t('common.email') }}
            </TableHeaderCell>
          </th>
          <th>
            <TableHeaderCell 
              field="role" 
              :sortField="filters.sort" 
              :sortDirection="filters.direction"
            >
              {{ t('common.role') }}
            </TableHeaderCell>
          </th>
          <th>
            <TableHeaderCell 
              field="permissions_count" 
              :sortField="filters.sort" 
              :sortDirection="filters.direction"
            >
              {{ t('roles.permissions_count') }}
            </TableHeaderCell>
          </th>
          <th>
            <TableHeaderCell 
              @click="sortAdmins('last_login_at')" 
              field="last_login_at" 
              :sortField="filters.sort" 
              :sortDirection="filters.direction"
            >
              {{ t('common.last_login') }}
            </TableHeaderCell>
          </th>
          <th>
            <TableHeaderCell 
              @click="sortAdmins('status')" 
              field="status" 
              :sortField="filters.sort" 
              :sortDirection="filters.direction"
            >
              {{ t('common.status') }}
            </TableHeaderCell>
          </th>
          <th></th>
        </tr>
      </thead>

      <tbody v-if="admins.loading || !admins.data.length">
        <tr>
          <td colspan="8">
            <Spinner v-if="admins.loading"/>
            <p v-else class="text-center py-8 text-gray-700">
              {{ t('common.no_data') }}
            </p>
          </td>
        </tr>
      </tbody>

      <tbody v-else>
        <tr v-for="admin in admins.data" :key="admin.id">
          <td class="border-b p-2">{{ admin.id }}</td>
          <td class="border-b p-2">{{ admin.name }}</td>
          <td class="border-b p-2">{{ admin.email }}</td>
          <td class="border-b p-2">
            <span v-for="role in admin.roles" :key="role.id" class="badge bg-primary me-1">
              {{ role.name }}
            </span>
            <span v-if="!admin.roles || admin.roles.length === 0" class="text-muted">-</span>
          </td>
          <td class="border-b p-2">
            <span class="badge bg-info">
              {{ admin.permissions?.length || 0 }}
            </span>
          </td>
          <td class="border-b p-2">{{ formatDate(admin.last_login_at) }}</td>
          <td class="border-b p-2">
            <div class="form-check form-switch">
              <input 
                class="form-check-input cursor-pointer" 
                type="checkbox" 
                role="switch" 
                :id="`switch-${admin.id}`"
                :checked="admin.status === 'active'"
                @change="toggleStatus(admin)"
                :disabled="!can('edit_user')"
              >
              <label class="form-check-label cursor-pointer" :for="`switch-${admin.id}`">
                {{ admin.status === 'active' ? t('common.active') : t('common.inactive') }}
              </label>
            </div>
          </td>
          <td class="border-b p-2">
            <Menu as="div" class="relative inline-block text-left">
              <div>
                <MenuButton class="inline-flex w-full justify-center gap-x-1.5 rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50">
                  <EllipsisVerticalIcon class="-mr-1 h-5 w-5 text-gray-400" aria-hidden="true" />
                </MenuButton>
              </div>

              <transition 
                enter-active-class="transition ease-out duration-100" 
                enter-from-class="transform opacity-0 scale-95" 
                enter-to-class="transform opacity-100 scale-100" 
                leave-active-class="transition ease-in duration-75" 
                leave-from-class="transform opacity-100 scale-100" 
                leave-to-class="transform opacity-0 scale-95"
              >
                <MenuItems class="absolute left-0 z-10 mt-2 w-56 origin-top-right rounded-md bg-white shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none">
                  <div class="py-1">
                    <MenuItem v-if="can('edit_user')" v-slot="{ active }">
                      <router-link 
                        :to="{ name: 'admins.edit', params: { uuid: admin.uuid } }" 
                        :class="[active ? 'bg-gray-100 text-gray-900' : 'text-gray-700', 'block px-4 py-2 text-sm']"
                      >
                        {{ t('common.edit') }}
                      </router-link>
                    </MenuItem>
                    <MenuItem v-if="can('delete_user')" v-slot="{ active }">
                      <a 
                        @click="confirmDelete(admin.uuid)" 
                        :class="[active ? 'bg-gray-100 text-gray-900' : 'text-gray-700', 'block px-4 py-2 text-sm cursor-pointer']"
                      >
                        {{ t('common.delete') }}
                      </a>
                    </MenuItem>
                  </div>
                </MenuItems>
              </transition>
            </Menu>
          </td>
        </tr>
      </tbody>
    </table>

    <!-- Pagination -->
    <Pagination 
      v-if="admins.meta && admins.meta.total" 
      :meta="admins.meta" 
      @page-change="pageChange"
    />

    <!-- Confirm Delete Dialog -->
    <ConfirmDialog
      :show="showConfirm"
      :title="t('common.confirm_delete')"
      :message="t('common.confirm_delete_message')"
      @onConfirmDialog="deleteAdmin"
      @onCancelDialog="showConfirm = false"
    />
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue';
import { useI18n } from 'vue-i18n';
import { useAdminStore } from '@/stores/admin';
import { useAuthStore } from '@/stores/auth';
import Spinner from '@/components/core/Spinner.vue';
import CustomInput from '@/components/core/CustomInput.vue';
import TableHeaderCell from '@/components/Table/TableHeaderCell.vue';
import Pagination from '@/components/core/Pagination.vue';
import ConfirmDialog from '@/components/core/ConfirmDialog.vue';
import { Menu, MenuButton, MenuItems, MenuItem } from '@headlessui/vue';
import { EllipsisVerticalIcon } from '@heroicons/vue/20/solid';
import { useToast } from "vue-toastification";
import debounce from 'lodash.debounce';

const { t } = useI18n();
const toast = useToast();
const authStore = useAuthStore();
const can = computed(() => authStore.can);
const adminStore = useAdminStore();
const admins = computed(() => adminStore.admins);
const filters = computed(() => adminStore.admins.filters);


const showConfirm = ref(false);
const deleteUuid = ref(null);

onMounted(() => {
  adminStore.getAdmins();
});

// Watch for search changes with debounce
const debouncedSearch = debounce(() => {
  adminStore.getAdmins({
    search: filters.value.search,
    per_page: filters.value.per_page,
    sort: filters.value.sort,
    direction: filters.value.direction,
  });
}, 500);

watch(() => filters.value.search, () => {
  debouncedSearch();
});

function updatePerPage() {
  adminStore.getAdmins({
    per_page: filters.value.per_page,
    search: filters.value.search,
    sort: filters.value.sort,
    direction: filters.value.direction,
  });
}

function sortAdmins(field) {
  const newDirection = filters.value.sort === field && filters.value.direction === 'asc' ? 'desc' : 'asc';
  filters.value.sort = field;
  filters.value.direction = newDirection;
  adminStore.getAdmins({
    sort: field,
    direction: newDirection,
    per_page: filters.value.per_page,
    search: filters.value.search,
  });
}

function pageChange(url) {
  adminStore.getAdmins({ url });
}

function confirmDelete(uuid) {
  deleteUuid.value = uuid;
  showConfirm.value = true;
}

async function deleteAdmin() {
  try {
    await adminStore.deleteAdmin(deleteUuid.value);
    toast.success(t('common.deleted_success'));
    showConfirm.value = false;
    adminStore.getAdmins();
  } catch (error) {
    toast.error(t('common.error_deleting'));
  }
}

async function toggleStatus(admin) {
  try {
    const newStatus = admin.status === 'active' ? 'inactive' : 'active';
    await adminStore.updateAdminStatus(admin.uuid, newStatus);
    toast.success(t('common.status_updated_success'));
    adminStore.getAdmins();
  } catch (error) {
    toast.error(t('common.error_updating'));
    adminStore.getAdmins(); // Reload to revert the change
  }
}

function formatDate(dateString) {
  if (!dateString) return '-';
  return new Date(dateString).toLocaleDateString();
}
</script>

