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
              @click="sortRoles('id')" 
              field="id" 
              :sortField="filters.sort" 
              :sortDirection="filters.direction"
            >
              {{ t('common.id') }}
            </TableHeaderCell>
          </th>
          <th>
            <TableHeaderCell 
              @click="sortRoles('name')" 
              field="name" 
              :sortField="filters.sort" 
              :sortDirection="filters.direction"
            >
              {{ t('roles.name') }}
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
              field="users_count" 
              :sortField="filters.sort" 
              :sortDirection="filters.direction"
            >
              {{ t('roles.users_count') }}
            </TableHeaderCell>
          </th>
          <th>
            <TableHeaderCell 
              @click="sortRoles('created_at')" 
              field="created_at" 
              :sortField="filters.sort" 
              :sortDirection="filters.direction"
            >
              {{ t('common.created_at') }}
            </TableHeaderCell>
          </th>
          <th></th>
        </tr>
      </thead>

      <tbody v-if="roles.loading || !roles.data.length">
        <tr>
          <td colspan="6">
            <Spinner v-if="roles.loading"/>
            <p v-else class="text-center py-8 text-gray-700">
              {{ t('common.no_data') }}
            </p>
          </td>
        </tr>
      </tbody>

      <tbody v-else>
        <tr v-for="role in roles.data" :key="role.id">
          <td class="border-b p-2">{{ role.id }}</td>
          <td class="border-b p-2">
            <span class="badge bg-primary">{{ role.name }}</span>
          </td>
          <td class="border-b p-2">{{ role.permissions_count || 0 }}</td>
          <td class="border-b p-2">{{ role.users_count || 0 }}</td>
          <td class="border-b p-2">{{ formatDate(role.created_at) }}</td>
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
                    <MenuItem v-if="can('edit_role')" v-slot="{ active }">
                      <router-link 
                        :to="{ name: 'roles.edit', params: { id: role.id } }" 
                        :class="[active ? 'bg-gray-100 text-gray-900' : 'text-gray-700', 'block px-4 py-2 text-sm']"
                      >
                        {{ t('common.edit') }}
                      </router-link>
                    </MenuItem>
                    <MenuItem v-if="can('delete_role') && !['admin', 'user'].includes(role.name)" v-slot="{ active }">
                      <a 
                        @click="confirmDelete(role.id)" 
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
      v-if="roles.meta && roles.meta.total" 
      :meta="roles.meta" 
      @page-change="pageChange"
    />

    <!-- Confirm Delete Dialog -->
    <ConfirmDialog
      :show="showConfirm"
      :title="t('common.confirm_delete')"
      :message="t('roles.confirm_delete_message')"
      @onConfirmDialog="deleteRole"
      @onCancelDialog="showConfirm = false"
    />
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue';
import { useI18n } from 'vue-i18n';
import { useRoleStore } from '@/stores/role';
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
const roleStore = useRoleStore();
const roles = computed(() => roleStore.roles);
const filters = computed(() => roleStore.roles.filters);

const showConfirm = ref(false);
const deleteId = ref(null);

onMounted(() => {
  roleStore.getRoles();
});

// Watch for search changes with debounce
const debouncedSearch = debounce(() => {
  roleStore.getRoles({
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
  roleStore.getRoles({
    per_page: filters.value.per_page,
    search: filters.value.search,
    sort: filters.value.sort,
    direction: filters.value.direction,
  });
}

function sortRoles(field) {
  const newDirection = filters.value.sort === field && filters.value.direction === 'asc' ? 'desc' : 'asc';
  filters.value.sort = field;
  filters.value.direction = newDirection;
  roleStore.getRoles({
    sort: field,
    direction: newDirection,
    per_page: filters.value.per_page,
    search: filters.value.search,
  });
}

function pageChange(url) {
  roleStore.getRoles({ url });
}

function confirmDelete(id) {
  deleteId.value = id;
  showConfirm.value = true;
}

async function deleteRole() {
  try {
    await roleStore.deleteRole(deleteId.value);
    toast.success(t('roles.role_deleted_success'));
    showConfirm.value = false;
    roleStore.getRoles();
  } catch (error) {
    toast.error(t('common.error_deleting'));
  }
}

function formatDate(dateString) {
  if (!dateString) return '-';
  return new Date(dateString).toLocaleDateString();
}
</script>

