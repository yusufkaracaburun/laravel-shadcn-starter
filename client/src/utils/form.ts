export function mapObjectDeep<T extends object>(obj: T, mapper: (value: any) => any): T {
  if (obj === null || typeof obj !== 'object') {
    return mapper(obj)
  }

  if (Array.isArray(obj)) {
    return obj.map((item) => mapObjectDeep(item, mapper)) as T
  }

  return Object.fromEntries(
    Object.entries(obj).map(([key, value]) => [key, mapObjectDeep(value, mapper)]),
  ) as T
}
