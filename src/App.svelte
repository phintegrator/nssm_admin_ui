<script>
  import { onMount } from 'svelte';

  let services = [];
  let isLoggedIn = localStorage.getItem('loggedIn') === 'true';
  let showInstallModal = false;
  let loginForm = { username: '', password: '' };
  let showLoginModal = false;
  let refreshRate = 5;
  let refreshInterval;
  let selectedServer = 'http://127.0.0.1:54321';
  let newService = {
    service_name: '',
    executable_path: '',
    startup_directory: '',
    arguments: ''
  };
  let message = '';
  let isRefreshing = false;
  let showDetailsModal = false;
  let selectedService = null;

  function showServiceDetails(service) {
    selectedService = service;
    showDetailsModal = true;
  }

  async function startService(serviceName) {
    if (!isLoggedIn) return;
    const response = await fetch(`${selectedServer}/services/${serviceName}/start`, { method: 'POST' });
    const data = await response.json();
    message = data.message || 'Error starting service.';
    fetchServices();
  }

  async function stopService(serviceName) {
    if (!isLoggedIn) return;
    const response = await fetch(`${selectedServer}/services/${serviceName}/stop`, { method: 'POST' });
    const data = await response.json();
    message = data.message || 'Error stopping service.';
    fetchServices();
  }

  async function removeService(serviceName) {
    if (!isLoggedIn) return;
    const response = await fetch(`${selectedServer}/services/${serviceName}`, { method: 'DELETE' });
    const data = await response.json();
    message = data.message || 'Error removing service.';
    fetchServices();
  }

  async function installService() {
    if (!isLoggedIn) return;
    const response = await fetch(`${selectedServer}/services`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(newService)
    });
    const data = await response.json();
    alert(data.message || 'Error installing service.');
    showInstallModal = false;
    fetchServices();
  }

  async function fetchServices() {
    try {
      isRefreshing = true;
      const response = await fetch(`${selectedServer}/services`);
      const data = await response.json();
      services = data.services;
    } catch (error) {
      console.error('Error fetching services:', error);
      message = 'Error connecting to server';
    } finally {
      setTimeout(() => {
        isRefreshing = false;
      }, 1500);
    }
  }

  function login() {
    if (loginForm.username === 'admin' && loginForm.password === 'admin') {
      isLoggedIn = true;
      localStorage.setItem('loggedIn', 'true');
      showLoginModal = false;
      message = 'Login successful!';
    } else {
      message = 'Invalid credentials.';
    }
  }

  function logout() {
    isLoggedIn = false;
    localStorage.removeItem('loggedIn');
    message = 'Logged out successfully.';
  }

  function startAutoRefresh() {
    if (refreshInterval) clearInterval(refreshInterval);
    refreshInterval = setInterval(() => {
      fetchServices();
    }, refreshRate * 1000);
  }

  onMount(() => {
    fetchServices();
    startAutoRefresh();
  });
</script>

<div class="max-w-7xl mx-auto bg-white shadow-xl rounded-xl p-6">
  <!-- Header -->
  <div class="flex justify-between items-center mb-6">
    <h1 class="text-2xl font-bold text-gray-800">🚀 NSSM Service Manager</h1>
    <div class="flex items-center space-x-3">
      {#if isLoggedIn}
        <div class="flex items-center space-x-3">
          <button class="text-sm bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg transition" on:click={() => showInstallModal = true}>Install New Service</button>
          <span class="text-sm text-gray-600">Welcome, Admin</span>
          <button class="text-sm bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg transition" on:click={logout}>Logout</button>
        </div>
      {:else}
        <button class="text-sm bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition" on:click={() => showLoginModal = true}>Login</button>
      {/if}
    </div>
  </div>

  <!-- Controls -->
  <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
    <div class="flex items-center space-x-3">
      {#if isRefreshing}
        <div class="flex items-center space-x-2">
          <div class="animate-spin h-5 w-5 border-t-2 border-blue-500 border-solid rounded-full"></div>
          <span class="text-blue-500 text-sm font-medium">Refreshing...</span>
        </div>
      {/if}
    </div>
    <div class="flex flex-col sm:flex-row gap-4">
      <div class="flex items-center space-x-2">
        <span class="text-sm text-gray-700 font-medium">Server:</span>
        <select class="border border-gray-300 p-2 text-sm rounded-lg focus:ring-2 focus:ring-blue-500" bind:value={selectedServer} on:change={fetchServices}>
          <option value="http://127.0.0.1:54321">Localhost (54321)</option>
          <option value="http://192.168.32.29:54321">192.168.32.29</option>
        </select>
      </div>
      <div class="flex items-center space-x-2">
        <span class="text-sm text-gray-700 font-medium">Auto Refresh:</span>
        <select class="border border-gray-300 p-2 text-sm rounded-lg focus:ring-2 focus:ring-blue-500" bind:value={refreshRate} on:change={startAutoRefresh}>
          <option value="5">5s</option>
          <option value="10">10s</option>
          <option value="30">30s</option>
          <option value="60">60s</option>
        </select>
      </div>
    </div>
  </div>

  <!-- Table -->
  <div class="table-container">
    <table class="w-full border-collapse border border-gray-200 text-sm">
      <thead>
        <tr class="bg-gray-200 text-left text-gray-700">
          <th class="border p-3 w-2/6">Service Name</th>
          <th class="border p-3 w-1/6">Status</th>
          <th class="border p-3 w-2/6">Arguments</th>
          <th class="border p-3 w-1/6">Actions</th>
        </tr>
      </thead>
      <tbody class="bg-white">
        {#each services as service (service.service_name)}
          <tr class="border-b hover:bg-gray-50">
            <td class="border p-3">
              <button class="text-blue-600 hover:underline" on:click={() => showServiceDetails(service)}>{service.service_name}</button>
            </td>
            <td class="border p-3">{service.Status}</td>
            <td class="border p-3">{service.Arguments}</td>
            <td class="border p-3">
              {#if isLoggedIn}
                <div class="flex space-x-2">
                  <button class="bg-green-500 hover:bg-green-600 text-white px-3 py-1 rounded text-xs transition" on:click={() => startService(service.service_name)}>Start</button>
                  <button class="bg-yellow-500 hover:bg-yellow-600 text-white px-3 py-1 rounded text-xs transition" on:click={() => stopService(service.service_name)}>Stop</button>
                  <button class="bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded text-xs transition" on:click={() => removeService(service.service_name)}>Remove</button>
                </div>
              {:else}
                <span class="text-gray-500 italic text-xs">Login required</span>
              {/if}
            </td>
          </tr>
        {/each}
      </tbody>
    </table>
  </div>

  <!-- Service Details Modal -->
  {#if showDetailsModal}
    <div class="fixed inset-0 bg-black bg-opacity-50 flex justify-center items-center" on:click={() => showDetailsModal = false}>
      <div class="bg-white p-6 rounded-xl shadow-2xl w-full max-w-md" on:click|stopPropagation>
        <h2 class="text-xl font-semibold mb-4 text-gray-800">📋 Service Details</h2>
        <div class="space-y-3">
          <div>
            <span class="font-medium text-gray-700">Service Name:</span>
            <p class="text-gray-600">{selectedService?.service_name}</p>
          </div>
          <div>
            <span class="font-medium text-gray-700">Status:</span>
            <p class="text-gray-600">{selectedService?.Status}</p>
          </div>
          <div>
            <span class="font-medium text-gray-700">Executable Path:</span>
            <p class="text-gray-600 break-all">{selectedService?.['Path']}</p>
          </div>
          <div>
            <span class="font-medium text-gray-700">Startup Directory:</span>
            <p class="text-gray-600 break-all">{selectedService?.['Startup Directory']}</p>
          </div>
          <div>
            <span class="font-medium text-gray-700">Arguments:</span>
            <p class="text-gray-600">{selectedService?.Arguments || 'None'}</p>
          </div>
        </div>
        <div class="flex justify-end mt-4">
          <button class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded transition" on:click={() => showDetailsModal = false}>Close</button>
        </div>
      </div>
    </div>
  {/if}

  <!-- Install Modal -->
  {#if showInstallModal}
    <div class="fixed inset-0 bg-black bg-opacity-50 flex justify-center items-center" on:click={() => showInstallModal = false}>
      <div class="bg-white p-6 rounded-xl shadow-2xl w-full max-w-md" on:click|stopPropagation>
        <h2 class="text-xl font-semibold mb-4 text-gray-800">🛠 Install New Service</h2>
        <input type="text" class="border p-2 rounded w-full mb-3 focus:ring-2 focus:ring-blue-500" placeholder="Service Name" bind:value={newService.service_name} />
        <input type="text" class="border p-2 rounded w-full mb-3 focus:ring-2 focus:ring-blue-500" placeholder="Executable Path" bind:value={newService.executable_path} />
        <input type="text" class="border p-2 rounded w-full mb-3 focus:ring-2 focus:ring-blue-500" placeholder="Startup Directory" bind:value={newService.startup_directory} />
        <input type="text" class="border p-2 rounded w-full mb-3 focus:ring-2 focus:ring-blue-500" placeholder="Arguments (Optional)" bind:value={newService.arguments} />
        <div class="flex justify-end space-x-3">
          <button class="text-gray-600 hover:text-gray-800" on:click={() => showInstallModal = false}>Cancel</button>
          <button class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded transition" on:click={installService}>Install</button>
        </div>
      </div>
    </div>
  {/if}

  <!-- Login Modal -->
  {#if showLoginModal}
    <div class="fixed inset-0 bg-black bg-opacity-50 flex justify-center items-center" on:click={() => showLoginModal = false}>
      <div class="bg-white p-6 rounded-xl shadow-2xl w-full max-w-md" on:click|stopPropagation>
        <h2 class="text-xl font-semibold mb-4 text-gray-800">🔐 Login</h2>
        <input type="text" class="border p-2 rounded w-full mb-3 focus:ring-2 focus:ring-blue-500" placeholder="Username" bind:value={loginForm.username} />
        <input type="password" class="border p-2 rounded w-full mb-3 focus:ring-2 focus:ring-blue-500" placeholder="Password" bind:value={loginForm.password} />
        <div class="flex justify-end space-x-3">
          <button class="text-gray-600 hover:text-gray-800" on:click={() => showLoginModal = false}>Cancel</button>
          <button class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded transition" on:click={login}>Login</button>
        </div>
      </div>
    </div>
  {/if}

  <!-- Notification -->
  {#if message}
    <div class="mt-6">
      <p class="p-3 bg-green-100 text-green-700 rounded-lg">{message}</p>
    </div>
  {/if}
</div>

<style>
  .table-container {
    max-height: 500px;
    overflow-y: auto;
    margin-top: 1rem;
  }
  table { min-width: 100%; }
  th {
    position: sticky;
    top: 0;
    z-index: 10;
    background: #e5e7eb;
  }
  td, th {
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
  }
</style>
