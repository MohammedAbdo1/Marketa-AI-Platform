<template>
  <div class="p-4">
    <h3 class="mb-4">Electronic Signature Component Examples</h3>
    
    <!-- Example 1: Default Signature -->
    <div class="mb-5">
      <h4>Default Signature (600x200)</h4>
      <ElectronicSignature 
        ref="defaultSignature"
        @signature-changed="onSignatureChanged"
        @signature-saved="onSignatureSaved"
      />
    </div>
    
    <!-- Example 2: Small Signature -->
    <div class="mb-5">
      <h4>Small Signature (400x150)</h4>
      <ElectronicSignature 
        :width="400"
        :height="150"
        stroke-color="#007bff"
        stroke-width="3"
        @signature-changed="onSignatureChanged"
        @signature-saved="onSignatureSaved"
      />
    </div>
    
    <!-- Example 3: Large Signature -->
    <div class="mb-5">
      <h4>Large Signature (800x300)</h4>
      <ElectronicSignature 
        :width="800"
        :height="300"
        stroke-color="#28a745"
        stroke-width="4"
        @signature-changed="onSignatureChanged"
        @signature-saved="onSignatureSaved"
      />
    </div>
    
    <!-- Example 4: With Existing Signature -->
    <div class="mb-5">
      <h4>With Existing Signature</h4>
      <ElectronicSignature 
        :existing-signature-url="existingSignatureUrl"
        stroke-color="#dc3545"
        @signature-changed="onSignatureChanged"
        @signature-saved="onSignatureSaved"
      />
    </div>
    
    <!-- Controls -->
    <div class="mt-4">
      <button @click="clearAllSignatures" class="btn btn-warning me-2">
        Clear All Signatures
      </button>
      <button @click="saveAllSignatures" class="btn btn-success me-2">
        Save All Signatures
      </button>
      <button @click="loadTestSignature" class="btn btn-info">
        Load Test Signature
      </button>
    </div>
    
    <!-- Signature Info -->
    <div v-if="signatureInfo" class="mt-4 p-3 bg-light rounded">
      <h5>Signature Information:</h5>
      <pre>{{ JSON.stringify(signatureInfo, null, 2) }}</pre>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import ElectronicSignature from './ElectronicSignature.vue';

const defaultSignature = ref(null);
const signatureInfo = ref(null);
const existingSignatureUrl = ref('data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjAwIiBoZWlnaHQ9IjEwMCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj4KICA8cmVjdCB3aWR0aD0iMjAwIiBoZWlnaHQ9IjEwMCIgZmlsbD0iI2ZmZmZmZiIgc3Ryb2tlPSIjMDAwIiBzdHJva2Utd2lkdGg9IjIiLz4KICA8dGV4dCB4PSIxMDAiIHk9IjUwIiB0ZXh0LWFuY2hvcj0ibWlkZGxlIiBkeT0iLjNlbSIgZm9udC1mYW1pbHk9IkFyaWFsLCBzYW5zLXNlcmlmIiBmb250LXNpemU9IjE0cHgiPuq4gOq4gOq4gOq4gDwvdGV4dD4KPC9zdmc+');

function onSignatureChanged(info) {
  signatureInfo.value = info;
  console.log('Signature changed:', info);
}

function onSignatureSaved(info) {
  signatureInfo.value = info;
  console.log('Signature saved:', info);
}

function clearAllSignatures() {
  if (defaultSignature.value) {
    defaultSignature.value.clearSignature();
  }
}

async function saveAllSignatures() {
  if (defaultSignature.value) {
    const file = await defaultSignature.value.saveSignature();
    console.log('Saved signature file:', file);
  }
}

function loadTestSignature() {
  // This would load a test signature URL
  existingSignatureUrl.value = 'https://via.placeholder.com/200x100/007bff/ffffff?text=Test+Signature';
}
</script>
