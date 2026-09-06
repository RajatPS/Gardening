# REST API

The application keeps its existing Blade, session, and web routes unchanged. Public read endpoints are versioned under `/api/v1` and return JSON.

## Products

Authentication: not required. Only active products with a non-empty name are exposed.

### `GET /api/v1/products`

Optional query parameters:

- `search`: match product name, category, or description.
- `category`: match category or product name.
- `sort_by`: `created_at`, `name`, `price`, `stock`, or `category`.
- `sort_order`: `asc` or `desc`.
- `per_page`: 1 through 50; defaults to 15.

Successful responses contain `success`, `message`, `data`, and pagination `meta` fields. Product data includes the public catalog fields, image URLs, stock state, care information, and no credentials or authentication fields.

### `GET /api/v1/products/{id-or-slug}`

Returns one active product by numeric ID or name slug such as `peace-lily`.

Not found:

```json
{
    "success": false,
    "message": "Product not found."
}
```

Invalid query parameters return HTTP `422` with `success: false`, a `Validation failed.` message, and an `errors` object.