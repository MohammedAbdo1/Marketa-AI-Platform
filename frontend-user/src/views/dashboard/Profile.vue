<template>
  <div class="profile-page">
    <div class="card card-flat header-card">
      <div class="card-body">
        <div class="header-content">
          <div>
            <h1 class="page-title">{{ $t('profile.title') }}</h1>
            <p class="text-secondary mt-1">{{ $t('profile.subtitle') }}</p>
          </div>
          <div class="membership-chip" v-if="subscriptionName">
            <i class="bx bx-crown"></i>
            <span>{{ subscriptionName }}</span>
          </div>
        </div>
      </div>
    </div>

    <div class="details-grid">
      <div class="card account-card">
        <div class="card-header">
          <h3 class="card-title">{{ $t('profile.account.heading') }}</h3>
        </div>
        <div class="card-body">
          <LoadingSpinner v-if="loading" size="md" />
          <template v-else>
            <div class="account-info">
              <div class="avatar">
                <span>{{ accountInitials }}</span>
              </div>
              <div class="flex-1">
                <label class="form-label" for="preferredName">
                  {{ $t('profile.account.preferred_name') }}
                </label>
                <input
                  id="preferredName"
                  type="text"
                  class="form-control"
                  v-model="preferredName"
                  :placeholder="$t('profile.account.preferred_name_placeholder')"
                />
                <div class="account-actions">
                  <button class="btn btn-ghost btn-sm" @click="triggerPhotoUpload">
                    {{ $t('profile.account.add_photo') }}
                  </button>
                  <button class="btn btn-ghost btn-sm" @click="handleCreatePortrait">
                    {{ $t('profile.account.create_portrait') }}
                  </button>
                  <button
                    class="btn btn-primary btn-sm"
                    :disabled="savingName || preferredName.trim() === user.name"
                    @click="handleSaveName"
                  >
                    {{ savingName ? $t('profile.account.saving') : $t('profile.account.save') }}
                  </button>
                </div>
                <input
                  ref="photoInput"
                  type="file"
                  class="d-none"
                  accept="image/*"
                  @change="handlePhotoSelected"
                />
              </div>
            </div>

            <div class="account-meta">
              <div>
                <h4 class="text-sm font-medium text-secondary">
                  {{ $t('profile.account.organization') }}
                </h4>
                <p class="text-base text-primary mt-1">{{ organizationName }}</p>
              </div>
              <div>
                <h4 class="text-sm font-medium text-secondary">
                  {{ $t('profile.account.email') }}
                </h4>
                <p class="text-base text-primary mt-1">{{ email }}</p>
              </div>
            </div>
          </template>
        </div>
      </div>

      <div class="card security-card">
        <div class="card-header">
          <h3 class="card-title">{{ $t('profile.security.heading') }}</h3>
        </div>
        <div class="card-body security-list">
          <div class="security-item">
            <div>
              <h4 class="security-title">{{ $t('profile.security.email.title') }}</h4>
              <p class="security-description">
                {{ $t('profile.security.email.description') }}
              </p>
              <p class="security-value text-primary">{{ email }}</p>
            </div>
            <button class="btn btn-ghost btn-sm" @click="handleChangeEmail">
              {{ $t('profile.security.email.change') }}
            </button>
          </div>

          <div class="security-item">
            <div>
              <h4 class="security-title">{{ $t('profile.security.password.title') }}</h4>
              <p class="security-description">
                {{ hasPassword ? $t('profile.security.password.description_set') : $t('profile.security.password.description') }}
              </p>
            </div>
            <button class="btn btn-ghost btn-sm" @click="handlePasswordAction">
              {{ hasPassword ? $t('profile.security.password.change') : $t('profile.security.password.add') }}
            </button>
          </div>

          <div class="security-item">
            <div>
              <h4 class="security-title">{{ $t('profile.security.twofactor.title') }}</h4>
              <p class="security-description">
                {{ $t('profile.security.twofactor.description') }}
              </p>
              <span
                class="status-chip"
                :class="twoFactorEnabled ? 'status-success' : 'status-muted'"
              >
                {{ twoFactorEnabled ? $t('profile.security.status_enabled') : $t('profile.security.status_disabled') }}
              </span>
            </div>
            <button
              class="btn btn-ghost btn-sm"
              :disabled="!hasPassword"
              :title="!hasPassword ? $t('profile.security.twofactor.password_required') : ''"
              @click="handleTwoFactor"
            >
              {{ $t('profile.security.twofactor.add') }}
            </button>
          </div>

          <div class="security-item">
            <div>
              <h4 class="security-title">{{ $t('profile.security.passkeys.title') }}</h4>
              <p class="security-description">
                {{ $t('profile.security.passkeys.description') }}
              </p>
              <span
                class="status-chip"
                :class="passkeyEnabled ? 'status-success' : 'status-muted'"
              >
                {{ passkeyEnabled ? $t('profile.security.status_enabled') : $t('profile.security.status_disabled') }}
              </span>
            </div>
            <button class="btn btn-ghost btn-sm" @click="handleAddPasskey">
              {{ $t('profile.security.passkeys.add') }}
            </button>
          </div>
        </div>
      </div>
    </div>

    <div class="card devices-card">
      <div class="card-header devices-header">
        <h3 class="card-title">{{ $t('profile.devices.title') }}</h3>
        <button class="btn btn-ghost btn-sm" @click="handleLogoutAllDevices">
          {{ $t('profile.devices.logout_all') }}
        </button>
      </div>
      <div class="card-body">
        <div class="table-responsive">
          <table class="devices-table">
            <thead>
              <tr>
                <th>{{ $t('profile.devices.device') }}</th>
                <th>{{ $t('profile.devices.last_active') }}</th>
                <th>{{ $t('profile.devices.location') }}</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="device in deviceRows" :key="device.id || device.name">
                <td>
                  <div class="device-name">
                    <i class="bx bx-laptop"></i>
                    <div>
                      <p class="text-base text-primary">
                        {{ device.name || $t('profile.devices.unknown_device') }}
                      </p>
                      <p class="text-sm text-secondary mt-1">
                        {{ device.platform || '-' }}
                      </p>
                    </div>
                    <span v-if="device.current" class="status-chip status-info">
                      {{ $t('profile.devices.this_device') }}
                    </span>
                  </div>
                </td>
                <td>{{ formatDateTime(device.last_active) }}</td>
                <td>{{ device.location || $t('profile.devices.unknown_location') }}</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <div class="card user-id-card">
      <div class="card-header">
        <h3 class="card-title">{{ $t('profile.user_id.title') }}</h3>
      </div>
      <div class="card-body user-id-body">
        <div class="user-id-value">{{ userId }}</div>
        <button class="btn btn-ghost btn-sm" @click="copyUserId">
          <i class="bx bx-copy"></i>
          {{ $t('profile.user_id.copy') }}
        </button>
      </div>
    </div>
  </div>

  <div v-if="showEmailModal" class="modal-backdrop" @click="closeEmailModal">
    <div class="modal modal-email" @click.stop>
      <div class="modal-header">
        <h3 class="modal-title">
          {{ emailModalStep === 'update' ? $t('profile.email_modal.update_title') : $t('profile.email_modal.verify_title') }}
        </h3>
        <button class="modal-close" @click="closeEmailModal">
          <i class="bx bx-x"></i>
        </button>
      </div>
      <div class="modal-body">
        <div v-if="emailModalStep === 'request'" class="email-step">
          <p v-html="$t('profile.email_modal.verify_description', { email: `<strong>${email}</strong>` })" />
          <button
            type="button"
            class="btn btn-primary"
            :disabled="sendingEmailCode"
            @click="handleSendVerificationCode"
          >
            {{ sendingEmailCode ? $t('profile.email_modal.sending_code') : $t('profile.email_modal.send_code') }}
          </button>
          <p class="text-xs text-tertiary">
            {{ $t('profile.email_modal.security_note') }}
          </p>
        </div>

        <div v-else-if="emailModalStep === 'verify'" class="email-form">
          <p v-html="$t('profile.email_modal.verify_sent_description', { email: `<strong>${email}</strong>` })" />
          <div class="inputs">
            <div>
              <label class="form-label" for="verificationCodeModal">
                {{ $t('profile.email_modal.code_label') }}
              </label>
              <input
                id="verificationCodeModal"
                type="text"
                class="form-control"
                v-model="verificationCode"
                :placeholder="$t('profile.email_modal.code_placeholder')"
              />
            </div>
          </div>
          <div class="modal-actions">
            <button type="button" class="btn btn-ghost" @click="closeEmailModal">
              {{ $t('common.cancel') }}
            </button>
            <button type="button" class="btn btn-primary" :disabled="verifyingCode" @click="handleVerifyCode">
              {{ verifyingCode ? $t('profile.email_modal.verifying') : $t('profile.email_modal.continue_button') }}
            </button>
          </div>
          <p class="text-xs text-tertiary">
            {{ $t('profile.email_modal.didnt_receive') }}
            <button
              type="button"
              class="btn btn-ghost btn-xs"
              :disabled="sendingEmailCode"
              @click="handleSendVerificationCode"
            >
              {{ sendingEmailCode ? $t('profile.email_modal.sending_code') : $t('profile.email_modal.resend_code') }}
            </button>
          </p>
        </div>

        <div v-else class="email-form">
          <div class="inputs">
            <div>
              <label class="form-label" for="newEmail">
                {{ $t('profile.email_modal.new_email_label') }}
              </label>
              <input
                id="newEmail"
                type="email"
                class="form-control"
                v-model="newEmail"
                :placeholder="$t('profile.email_modal.new_email_placeholder')"
              />
            </div>
            <div>
              <label class="form-label" for="confirmEmail">
                {{ $t('profile.email_modal.confirm_email_label') }}
              </label>
              <input
                id="confirmEmail"
                type="email"
                class="form-control"
                v-model="confirmEmail"
                :placeholder="$t('profile.email_modal.confirm_email_placeholder')"
              />
            </div>
          </div>
          <div class="modal-actions">
            <button type="button" class="btn btn-ghost" @click="closeEmailModal">
              {{ $t('common.cancel') }}
            </button>
            <button type="button" class="btn btn-primary" :disabled="updatingEmail" @click="handleConfirmEmailChange">
              {{ updatingEmail ? $t('profile.email_modal.updating') : $t('profile.email_modal.update_button') }}
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div v-if="showPasswordModal" class="modal-backdrop" @click="closePasswordModal">
    <div class="modal modal-password" @click.stop>
      <div class="modal-header">
        <h3 class="modal-title">
          {{ isPasswordChange ? $t('profile.password_modal.change_title') : $t('profile.password_modal.set_title') }}
        </h3>
        <button class="modal-close" :disabled="passwordLoading" @click="closePasswordModal">
          <i class="bx bx-x"></i>
        </button>
      </div>
      <div class="modal-body">
        <p class="text-secondary">
          {{ $t('profile.password_modal.instructions') }}
        </p>
        <form class="password-form" @submit.prevent="submitPasswordChange">
          <div v-if="isPasswordChange" class="form-group">
            <label class="form-label" for="currentPassword">
              {{ $t('profile.password_modal.current_label') }}
            </label>
            <input
              id="currentPassword"
              type="password"
              class="form-control"
              v-model="passwordForm.current_password"
              autocomplete="current-password"
              :disabled="passwordLoading"
            />
          </div>
          <div class="form-group">
            <label class="form-label" for="newPassword">
              {{ $t('profile.password_modal.new_label') }}
            </label>
            <input
              id="newPassword"
              type="password"
              class="form-control"
              v-model="passwordForm.password"
              autocomplete="new-password"
              :disabled="passwordLoading"
            />
          </div>
          <div class="form-group">
            <label class="form-label" for="confirmPassword">
              {{ $t('profile.password_modal.confirm_label') }}
            </label>
            <input
              id="confirmPassword"
              type="password"
              class="form-control"
              v-model="passwordForm.password_confirmation"
              autocomplete="new-password"
              :disabled="passwordLoading"
            />
          </div>
          <p v-if="passwordError" class="text-error text-sm">
            {{ passwordError }}
          </p>
          <div class="modal-actions">
            <button type="button" class="btn btn-ghost" :disabled="passwordLoading" @click="closePasswordModal">
              {{ $t('common.cancel') }}
            </button>
            <button type="submit" class="btn btn-primary" :disabled="passwordLoading">
              <span v-if="passwordLoading" class="loader loader-xs"></span>
              {{
                isPasswordChange
                  ? $t('profile.password_modal.save_button')
                  : $t('profile.password_modal.set_button')
              }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, onMounted, reactive, ref } from 'vue'
import { useI18n } from 'vue-i18n'
import axios from '@/axios'
import { useAuthStore } from '@/stores/auth'
import { useToast } from 'vue-toastification'
import LoadingSpinner from '@/components/shared/LoadingSpinner.vue'

const authStore = useAuthStore()
const { t, locale } = useI18n()
const toast = useToast()

const loading = ref(false)
const savingName = ref(false)
const preferredName = ref('')
const photoInput = ref(null)
const showEmailModal = ref(false)
const emailModalStep = ref('request')
const sendingEmailCode = ref(false)
const verifyingCode = ref(false)
const updatingEmail = ref(false)
const verificationCode = ref('')
const newEmail = ref('')
const confirmEmail = ref('')
const verificationToken = ref('')
const showPasswordModal = ref(false)
const passwordForm = reactive({
  current_password: '',
  password: '',
  password_confirmation: '',
})
const passwordLoading = ref(false)
const passwordError = ref('')

const user = computed(() => authStore.currentUser || {})

const email = computed(() => user.value?.email || t('profile.common.not_set'))
const organizationName = computed(() => user.value?.organization?.name || t('profile.common.not_set'))
const subscriptionName = computed(() => user.value?.active_subscription?.plan?.name || t('profile.account.plan_free'))
const userId = computed(() => user.value?.uuid || user.value?.id || '')
const hasPassword = computed(() => Boolean(user.value?.has_password))
const twoFactorEnabled = computed(() => Boolean(user.value?.two_factor_enabled))
const passkeyEnabled = computed(() => Boolean(user.value?.passkeys_enabled))
const isPasswordChange = computed(() => hasPassword.value)

const accountInitials = computed(() => {
  const name = preferredName.value || user.value?.name || ''
  if (!name) return 'M'
  const parts = name.trim().split(' ')
  if (parts.length === 1) return parts[0].charAt(0).toUpperCase()
  return (parts[0].charAt(0) + parts[parts.length - 1].charAt(0)).toUpperCase()
})

const deviceRows = computed(() => {
  if (Array.isArray(user.value?.devices) && user.value.devices.length > 0) {
    return user.value.devices
  }

  const platform =
    typeof navigator !== 'undefined'
      ? [navigator.platform, navigator.userAgent].filter(Boolean).join(' • ')
      : t('profile.devices.unknown_platform')

  return [
    {
      id: 'current-device',
      name: t('profile.devices.default_name'),
      platform,
      last_active: user.value?.last_login_at || new Date().toISOString(),
      location: user.value?.last_known_location || t('profile.devices.unknown_location'),
      current: true,
    },
  ]
})

const formatDateTime = (value) => {
  if (!value) {
    return t('profile.common.just_now')
  }

  try {
    const formatter = new Intl.DateTimeFormat(locale.value || 'en', {
      year: 'numeric',
      month: 'short',
      day: 'numeric',
      hour: '2-digit',
      minute: '2-digit',
    })
    return formatter.format(new Date(value))
  } catch (error) {
    console.error('Failed to format date', error)
    return value
  }
}

const loadProfile = async () => {
  loading.value = true
  try {
    await authStore.fetchFullProfile()
    preferredName.value = user.value?.name || ''
  } catch (error) {
    console.error('Failed to load profile', error)
    toast.error(t('profile.notifications.load_failed'))
  } finally {
    loading.value = false
  }
}

const handleSaveName = async () => {
  if (!preferredName.value.trim()) {
    toast.error(t('profile.notifications.name_required'))
    return
  }

  if (preferredName.value === user.value?.name && !user.value?.preferred_name) {
    toast.info(t('profile.notifications.no_changes'))
    return
  }

  savingName.value = true
  try {
    await authStore.updateProfile({ name: preferredName.value.trim() })
    toast.success(t('profile.notifications.profile_updated'))
  } catch (error) {
    console.error('Failed to update profile', error)
    toast.error(t('profile.notifications.update_failed'))
  } finally {
    savingName.value = false
  }
}

const triggerPhotoUpload = () => {
  photoInput.value?.click()
}

const handlePhotoSelected = (event) => {
  const file = event.target?.files?.[0]
  if (!file) return

  toast.info(t('profile.notifications.avatar_coming_soon'))
  event.target.value = ''
}

const handleCreatePortrait = () => {
  toast.info(t('profile.notifications.portrait_coming_soon'))
}

const handleChangeEmail = () => {
  showEmailModal.value = true
  emailModalStep.value = 'request'
  verificationCode.value = ''
  newEmail.value = ''
  confirmEmail.value = ''
  verificationToken.value = ''
}

const resetPasswordForm = () => {
  passwordForm.current_password = ''
  passwordForm.password = ''
  passwordForm.password_confirmation = ''
  passwordError.value = ''
}

const handlePasswordAction = () => {
  resetPasswordForm()
  showPasswordModal.value = true
}

const closePasswordModal = () => {
  if (passwordLoading.value) return
  showPasswordModal.value = false
  resetPasswordForm()
}

const meetsPasswordPolicy = (value) => {
  if (!value) return false
  const trimmed = value.trim()
  const longEnough = trimmed.length >= 15
  const strongEnough = trimmed.length >= 8 && /[A-Za-z]/.test(trimmed) && /\d/.test(trimmed)
  return longEnough || strongEnough
}

const submitPasswordChange = async () => {
  passwordError.value = ''

  if (isPasswordChange.value && !passwordForm.current_password.trim()) {
    passwordError.value = t('profile.password_modal.error_current_required')
    toast.error(passwordError.value)
    return
  }

  const trimmedPassword = passwordForm.password.trim()
  const trimmedConfirmation = passwordForm.password_confirmation.trim()

  if (!trimmedPassword) {
    passwordError.value = t('profile.password_modal.error_policy')
    toast.error(passwordError.value)
    return
  }

  if (!meetsPasswordPolicy(trimmedPassword)) {
    passwordError.value = t('profile.password_modal.error_policy')
    toast.error(passwordError.value)
    return
  }

  if (trimmedPassword !== trimmedConfirmation) {
    passwordError.value = t('profile.password_modal.error_mismatch')
    toast.error(passwordError.value)
    return
  }

  const payload = {
    password: trimmedPassword,
    password_confirmation: trimmedConfirmation,
  }

  if (isPasswordChange.value) {
    payload.current_password = passwordForm.current_password.trim()
  }

  passwordLoading.value = true

  try {
    await authStore.updateProfile(payload)
    await authStore.fetchFullProfile()
    toast.success(
      t(isPasswordChange.value ? 'profile.password_modal.success_change' : 'profile.password_modal.success_set')
    )
    showPasswordModal.value = false
    resetPasswordForm()
    return
  } catch (error) {
    const errors = error.response?.data?.errors || {}
    let message =
      errors.current_password?.[0] ||
      errors.password?.[0] ||
      errors.password_confirmation?.[0] ||
      error.response?.data?.message ||
      t('profile.notifications.update_failed')

    if (message === 'The current password is incorrect.') {
      message = t('profile.password_modal.error_current_invalid')
    } else if (message === 'Current password is required.') {
      message = t('profile.password_modal.error_current_required')
    }

    passwordError.value = message
    toast.error(message)
  } finally {
    passwordLoading.value = false
  }
}

const handleTwoFactor = () => {
  if (!hasPassword.value) {
    toast.error(t('profile.notifications.password_required'))
    return
  }
  toast.info(t('profile.notifications.twofactor_coming_soon'))
}

const handleAddPasskey = () => {
  toast.info(t('profile.notifications.passkey_coming_soon'))
}

const handleLogoutAllDevices = () => {
  toast.info(t('profile.notifications.logout_all_coming_soon'))
}

const copyUserId = async () => {
  if (!userId.value) {
    toast.error(t('profile.notifications.no_user_id'))
    return
  }

  try {
    await navigator.clipboard.writeText(userId.value)
    toast.success(t('profile.notifications.user_id_copied'))
  } catch (error) {
    console.error('Failed to copy user id', error)
    toast.error(t('profile.notifications.copy_failed'))
  }
}

const handleSendVerificationCode = async () => {
  if (!email.value) {
    toast.error(t('profile.notifications.no_user_id'))
    return
  }

  try {
    sendingEmailCode.value = true
    await axios.post('/profile/email-verification/request')
    toast.success(t('profile.email_modal.code_sent', { email: email.value }))
    verificationCode.value = ''
    emailModalStep.value = 'verify'
  } catch (error) {
    console.error('Failed to send verification code', error)
    toast.error(error.response?.data?.message || t('profile.email_modal.code_failed'))
  } finally {
    sendingEmailCode.value = false
  }
}

const handleVerifyCode = async () => {
  if (!verificationCode.value.trim()) {
    toast.error(t('profile.email_modal.code_required'))
    return
  }

  verifyingCode.value = true
  try {
    const response = await axios.post('/profile/email-verification/verify', {
      code: verificationCode.value.trim(),
    })
    verificationToken.value = response.data?.token || ''
    toast.success(t('profile.email_modal.code_verified'))
    emailModalStep.value = 'update'
  } catch (error) {
    console.error('Failed to verify code', error)
    toast.error(error.response?.data?.message || error.response?.data?.errors?.code?.[0] || t('profile.email_modal.code_failed'))
  } finally {
    verifyingCode.value = false
  }
}

const handleConfirmEmailChange = async () => {
  if (!verificationToken.value) {
    toast.error(t('profile.email_modal.code_required'))
    return
  }

  if (!newEmail.value.trim()) {
    toast.error(t('profile.email_modal.new_email_required'))
    return
  }

  if (newEmail.value.trim().toLowerCase() === String(user.value?.email || '').toLowerCase()) {
    toast.error(t('profile.notifications.no_changes'))
    return
  }

  if (newEmail.value.trim() !== confirmEmail.value.trim()) {
    toast.error(t('profile.email_modal.email_mismatch'))
    return
  }

  updatingEmail.value = true
  try {
    await authStore.updateProfile({
      email: newEmail.value.trim(),
      verification_token: verificationToken.value,
    })
    toast.success(t('profile.notifications.profile_updated'))
    await authStore.fetchFullProfile()
    showEmailModal.value = false
    verificationCode.value = ''
    verificationToken.value = ''
    newEmail.value = ''
    confirmEmail.value = ''
  } catch (error) {
    console.error('Failed to update email', error)
    toast.error(
      error.response?.data?.message ||
        error.response?.data?.errors?.email?.[0] ||
        error.response?.data?.errors?.verification_token?.[0] ||
        t('profile.notifications.update_failed')
    )
  } finally {
    updatingEmail.value = false
  }
}

const closeEmailModal = () => {
  if (updatingEmail.value || sendingEmailCode.value || verifyingCode.value) return
  showEmailModal.value = false
  emailModalStep.value = 'request'
}

onMounted(() => {
  loadProfile()
})
</script>
.modal-email {
  max-width: 420px;
}

.modal-password {
  max-width: 420px;
}

.password-form {
  display: flex;
  flex-direction: column;
  gap: var(--space-4);
  margin-top: var(--space-4);
}

.password-form .form-group {
  margin-bottom: 0;
}

.email-step {
  display: flex;
  flex-direction: column;
  gap: var(--space-4);
}

.email-step p {
  color: var(--color-text-secondary);
  line-height: 1.5;
}

.email-form {
  display: flex;
  flex-direction: column;
  gap: var(--space-4);
}

.email-form .inputs {
  display: flex;
  flex-direction: column;
  gap: var(--space-3);
}

.email-form .modal-actions {
  display: flex;
  justify-content: flex-end;
  gap: var(--space-2);
}

.email-form .btn-xs {
  padding: var(--space-1) var(--space-2);
  font-size: var(--text-xs);
}

.email-form .text-xs .btn {
  margin-inline-start: var(--space-1);
}

.modal-actions {
  display: flex;
  justify-content: flex-end;
  gap: var(--space-2);
  margin-top: var(--space-4);
}

<style scoped>
.profile-page {
  display: flex;
  flex-direction: column;
  gap: var(--space-6);
}

.header-card {
  border: none;
}

.header-content {
  display: flex;
  flex-direction: column;
  gap: var(--space-3);
}

.membership-chip {
  display: inline-flex;
  align-items: center;
  gap: var(--space-2);
  padding: var(--space-2) var(--space-3);
  border-radius: var(--radius-lg);
  background: var(--color-bg-secondary);
  color: var(--color-brand-primary);
  font-weight: 600;
  align-self: flex-start;
}

.details-grid {
  display: grid;
  gap: var(--space-6);
}

@media (min-width: 1024px) {
  .details-grid {
    grid-template-columns: minmax(0, 7fr) minmax(0, 5fr);
  }

  .header-content {
    flex-direction: row;
    justify-content: space-between;
    align-items: center;
  }
}

.account-info {
  display: flex;
  flex-direction: column;
  gap: var(--space-4);
}

@media (min-width: 768px) {
  .account-info {
    flex-direction: row;
    align-items: center;
  }
}

.avatar {
  width: 72px;
  height: 72px;
  border-radius: 50%;
  background: var(--color-bg-secondary);
  color: var(--color-brand-primary);
  display: inline-flex;
  align-items: center;
  justify-content: center;
  font-weight: 700;
  font-size: 1.5rem;
}

.account-actions {
  display: flex;
  flex-wrap: wrap;
  gap: var(--space-2);
  margin-top: var(--space-3);
}

.account-meta {
  margin-top: var(--space-6);
  display: grid;
  gap: var(--space-4);
}

@media (min-width: 768px) {
  .account-meta {
    grid-template-columns: repeat(2, minmax(0, 1fr));
  }
}

.security-list {
  display: flex;
  flex-direction: column;
  gap: var(--space-5);
}

.security-item {
  display: flex;
  flex-direction: column;
  gap: var(--space-3);
}

@media (min-width: 768px) {
  .security-item {
    flex-direction: row;
    justify-content: space-between;
    align-items: flex-start;
  }
}

.security-title {
  font-size: var(--text-base);
  font-weight: 600;
}

.security-description {
  color: var(--color-text-secondary);
  font-size: var(--text-sm);
  margin-top: var(--space-2);
}

.security-value {
  margin-top: var(--space-2);
}

.status-chip {
  display: inline-flex;
  align-items: center;
  gap: var(--space-1);
  font-size: var(--text-xs);
  font-weight: 600;
  padding: var(--space-1) var(--space-2);
  border-radius: var(--radius-md);
  margin-top: var(--space-2);
}

.status-success {
  background: rgba(12, 166, 120, 0.12);
  color: #0ca678;
}

.status-muted {
  background: var(--color-bg-secondary);
  color: var(--color-text-tertiary);
}

.status-info {
  background: rgba(11, 110, 153, 0.12);
  color: var(--color-brand-primary);
}

.devices-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.devices-table {
  width: 100%;
  border-collapse: collapse;
}

.devices-table th,
.devices-table td {
  text-align: left;
  padding: var(--space-3) 0;
  border-bottom: 1px solid var(--color-border-subtle);
}

.devices-table th {
  color: var(--color-text-tertiary);
  font-weight: 500;
  font-size: var(--text-sm);
}

.devices-table td {
  font-size: var(--text-sm);
  color: var(--color-text-secondary);
}

.device-name {
  display: flex;
  align-items: center;
  gap: var(--space-3);
}

.device-name i {
  font-size: 1.4rem;
  color: var(--color-brand-primary);
}

.user-id-body {
  display: flex;
  flex-direction: column;
  gap: var(--space-3);
}

@media (min-width: 768px) {
  .user-id-body {
    flex-direction: row;
    align-items: center;
    justify-content: space-between;
  }
}

.user-id-value {
  font-family: var(--font-mono, 'SFMono-Regular', Consolas, 'Liberation Mono', Menlo, monospace);
  font-size: var(--text-sm);
  padding: var(--space-3);
  background: var(--color-bg-secondary);
  border-radius: var(--radius-md);
  overflow-wrap: anywhere;
}
</style>
