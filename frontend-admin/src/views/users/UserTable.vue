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
              @click="sortUsers('id')" 
              field="id" 
              :sortField="filters.sort" 
              :sortDirection="filters.direction"
            >
              {{ t('common.id') }}
            </TableHeaderCell>
          </th>
          <th>
            <TableHeaderCell 
              @click="sortUsers('name')" 
              field="name" 
              :sortField="filters.sort" 
              :sortDirection="filters.direction"
            >
              {{ t('users.name') }}
            </TableHeaderCell>
          </th>
          <th>
            <TableHeaderCell 
              @click="sortUsers('email')" 
              field="email" 
              :sortField="filters.sort" 
              :sortDirection="filters.direction"
            >
              {{ t('users.email') }}
            </TableHeaderCell>
          </th>
          <th>
            <TableHeaderCell 
              field="phone" 
              :sortField="filters.sort" 
              :sortDirection="filters.direction"
            >
              {{ t('users.phone') }}
            </TableHeaderCell>
          </th>
          <th>
            <TableHeaderCell 
              @click="sortUsers('status')" 
              field="status" 
              :sortField="filters.sort" 
              :sortDirection="filters.direction"
            >
              {{ t('users.status') }}
            </TableHeaderCell>
          </th>
          <th>
            <TableHeaderCell 
              @click="sortUsers('created_at')" 
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

      <tbody v-if="users.loading || !users.data.length">
        <tr>
          <td colspan="7">
            <Spinner v-if="users.loading"/>
            <p v-else class="text-center py-8 text-gray-700">
              {{ t('common.no_data') }}
            </p>
          </td>
        </tr>
      </tbody>

      <tbody v-else>
        <tr v-for="user in users.data" :key="user.id">
          <td class="border-b p-2">{{ user.id }}</td>
          <td class="border-b p-2">{{ user.name }}</td>
          <td class="border-b p-2">{{ user.email }}</td>
          <td class="border-b p-2">{{ user.phone || '-' }}</td>
          <td class="border-b p-2">
            <div class="form-check form-switch">
              <input 
                class="form-check-input cursor-pointer" 
                type="checkbox" 
                role="switch" 
                :id="`switch-${user.id}`"
                :checked="user.status === 'active'"
                @change="toggleStatus(user)"
                :disabled="!can('users.edit')"
              >
              <label class="form-check-label cursor-pointer" :for="`switch-${user.id}`">
                {{ user.status === 'active' ? t('common.active') : t('common.inactive') }}
              </label>
            </div>
          </td>
          <td class="border-b p-2">{{ formatDate(user.created_at) }}</td>
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
                    <MenuItem v-if="can('users.edit')" v-slot="{ active }">
                      <router-link 
                        :to="{ name: 'users.edit', params: { uuid: user.uuid } }" 
                        :class="[active ? 'bg-gray-100 text-gray-900' : 'text-gray-700', 'block px-4 py-2 text-sm']"
                      >
                        {{ t('common.edit') }}
                      </router-link>
                    </MenuItem>
                    <MenuItem v-if="can('users.delete')" v-slot="{ active }">
                      <a 
                        @click="confirmDelete(user.uuid)" 
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
      v-if="users.meta && users.meta.total" 
      :meta="users.meta" 
      @page-change="pageChange"
    />

    <!-- Confirm Delete Dialog -->
    <ConfirmDialog
      :show="showConfirm"
      :title="t('common.confirm_delete')"
      :message="t('users.confirm_delete_message')"
      @onConfirmDialog="deleteUser"
      @onCancelDialog="showConfirm = false"
    />
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue';
import { useI18n } from 'vue-i18n';
import { useUserStore } from '@/stores/user';
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
const userStore = useUserStore();
const users = computed(() => userStore.users);
const filters = computed(() => userStore.users.filters);

const showConfirm = ref(false);
const deleteUuid = ref(null);

onMounted(() => {
  userStore.getUsers();
});

// Watch for search changes with debounce
const debouncedSearch = debounce(() => {
  userStore.getUsers({
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
  userStore.getUsers({
    per_page: filters.value.per_page,
    search: filters.value.search,
    sort: filters.value.sort,
    direction: filters.value.direction,
  });
}

function sortUsers(field) {
  const newDirection = filters.value.sort === field && filters.value.direction === 'asc' ? 'desc' : 'asc';
  filters.value.sort = field;
  filters.value.direction = newDirection;
  userStore.getUsers({
    sort: field,
    direction: newDirection,
    per_page: filters.value.per_page,
    search: filters.value.search,
  });
}

function pageChange(url) {
  userStore.getUsers({ url });
}

function confirmDelete(uuid) {
  deleteUuid.value = uuid;
  showConfirm.value = true;
}

async function deleteUser() {
  try {
    await userStore.deleteUser(deleteUuid.value);
    toast.success(t('users.user_deleted_success'));
    showConfirm.value = false;
    userStore.getUsers();
  } catch (error) {
    toast.error(t('common.error_deleting'));
  }
}

async function toggleStatus(user) {
  try {
    const newStatus = user.status === 'active' ? 'inactive' : 'active';
    await userStore.updateUserStatus(user.uuid, newStatus);
    toast.success(t('users.status_updated_success'));
    userStore.getUsers();
  } catch (error) {
    toast.error(t('common.error_updating'));
    userStore.getUsers(); // Reload to revert the change
  }
}

function formatDate(dateString) {
  if (!dateString) return '-';
  return new Date(dateString).toLocaleDateString();
}
</script>
