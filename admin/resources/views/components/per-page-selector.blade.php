@props(['paginator', 'currentPerPage' => 50])

<div class="flex items-center space-x-2 text-sm text-gray-700">
    <label for="per_page" class="font-medium">Show:</label>
    <select 
        id="per_page" 
        name="per_page" 
        onchange="updatePerPage(this.value)"
        class="px-2 py-1 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm"
    >
        <option value="10" {{ $currentPerPage == 10 ? 'selected' : '' }}>10</option>
        <option value="25" {{ $currentPerPage == 25 ? 'selected' : '' }}>25</option>
        <option value="50" {{ $currentPerPage == 50 ? 'selected' : '' }}>50</option>
        <option value="100" {{ $currentPerPage == 100 ? 'selected' : '' }}>100</option>
        <option value="200" {{ $currentPerPage == 200 ? 'selected' : '' }}>200</option>
    </select>
    <span>per page</span>
</div>

<script>
function updatePerPage(perPage) {
    const url = new URL(window.location);
    url.searchParams.set('per_page', perPage);
    url.searchParams.delete('page'); // Reset to first page
    window.location.href = url.toString();
}
</script>
