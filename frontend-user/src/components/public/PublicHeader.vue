<template>
  <header class="public-header" :class="{ scrolled: isScrolled }">
    <div class="container">
      <nav class="navbar">
        <router-link to="/" class="logo">
          <img src="@/assets/logo.png" alt="Logo" class="logo-img">
        </router-link>
        
        <div class="nav-menu" :class="{ active: menuOpen }">
          <router-link to="/" class="nav-link">{{ $t('nav.home') }}</router-link>
          <router-link to="/about" class="nav-link">{{ $t('nav.about') }}</router-link>
          <router-link to="/pricing" class="nav-link">{{ $t('nav.pricing') }}</router-link>
          <router-link to="/faq" class="nav-link">{{ $t('nav.faq') }}</router-link>
        </div>
        
        <div class="nav-actions">
          <button @click="toggleLanguage" class="btn-language">
            {{ currentLocale === 'ar' ? 'EN' : 'عربي' }}
          </button>
          
          <router-link to="/auth/login" class="btn btn-outline d-none d-md-block">
            {{ $t('nav.login') }}
          </router-link>
          
          <router-link to="/auth/register" class="btn btn-primary">
            {{ $t('nav.start_free') }}
          </router-link>
          
          <button class="btn-menu" @click="menuOpen = !menuOpen">
            <i class="bx" :class="menuOpen ? 'bx-x' : 'bx-menu'"></i>
          </button>
        </div>
      </nav>
    </div>
  </header>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue'
import { useI18n } from 'vue-i18n'
import { setLocale } from '@/i18n'

const { locale: currentLocale } = useI18n()
const isScrolled = ref(false)
const menuOpen = ref(false)

const handleScroll = () => {
  isScrolled.value = window.scrollY > 50
}

const toggleLanguage = () => {
  const newLocale = currentLocale.value === 'ar' ? 'en' : 'ar'
  setLocale(newLocale)
}

onMounted(() => {
  window.addEventListener('scroll', handleScroll)
})

onUnmounted(() => {
  window.removeEventListener('scroll', handleScroll)
})
</script>

<style scoped>
.public-header {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  z-index: var(--z-fixed);
  background: transparent;
  transition: var(--transition-slow);
  padding: var(--space-4) 0;
}

.public-header.scrolled {
  background: var(--color-bg-primary);
  box-shadow: var(--shadow-md);
  border-bottom: 1px solid var(--color-border-light);
}

.navbar {
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.logo {
  text-decoration: none;
  color: var(--color-text-primary);
  font-size: var(--text-2xl);
  font-weight: var(--font-bold);
}

.nav-menu {
  display: flex;
  gap: var(--space-8);
  align-items: center;
}

.nav-link {
  text-decoration: none;
  color: var(--color-text-primary);
  font-weight: var(--font-medium);
  font-size: var(--text-sm);
  transition: var(--transition-fast);
  padding: var(--space-2);
  border-radius: var(--radius-md);
}

.nav-link:hover {
  color: var(--color-brand-primary);
  background: var(--color-bg-hover);
}

.nav-actions {
  display: flex;
  gap: var(--space-3);
  align-items: center;
}

/* زر اللغة فقط (باقي الأزرار من Design System) */
.btn-language {
  height: 32px;
  padding: 0 var(--space-4);
  border: 1px solid var(--color-border-medium);
  border-radius: var(--radius-md);
  background: var(--color-bg-primary);
  color: var(--color-text-primary);
  font-size: var(--text-sm);
  font-weight: var(--font-medium);
  cursor: pointer;
  transition: var(--transition-fast);
}

.btn-language:hover {
  background: var(--color-bg-hover);
  border-color: var(--color-brand-primary);
  color: var(--color-brand-primary);
}

.btn-menu {
  display: none;
  background: none;
  border: none;
  font-size: var(--text-2xl);
  color: var(--color-text-primary);
  cursor: pointer;
  padding: var(--space-2);
  border-radius: var(--radius-md);
}

.btn-menu:hover {
  background: var(--color-bg-hover);
}

/* Mobile */
@media (max-width: 768px) {
  .nav-menu {
    display: none;
  }
  
  .btn-menu {
    display: block;
  }
  
  .nav-menu.active {
    display: flex;
    flex-direction: column;
    position: absolute;
    top: 100%;
    left: 0;
    right: 0;
    background: var(--color-bg-primary);
    padding: var(--space-4);
    box-shadow: var(--shadow-lg);
    border-bottom: 1px solid var(--color-border-light);
  }
}
</style>

