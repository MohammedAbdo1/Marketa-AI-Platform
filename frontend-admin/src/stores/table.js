export default function createTableState(customFilters = {}) {
  return {
    loading: false,
    data: [],
    meta: {
      from: null,
      to: null,
      total: null,
      current_page: 1,
      last_page: 1,
      links: [],
    },
    filters: {
      search: "",
      sort: "id",
      direction: "desc",
      page: 1,
      per_page: 10,
      ...customFilters,
    },
  };
}

