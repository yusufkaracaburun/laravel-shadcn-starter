import common from './common.json'
import invoices from './invoices.json'
import items from './items.json'
import marketing from './marketing.json'
import premium from './premium.json'
import sidebar from './sidebar.json'
import users from './users.json'
import vehicles from './vehicles.json'

/**
 * Merged Chinese translations
 * All translation files are imported and merged into a single object
 */
const zh = {
  ...common,
  ...premium,
  ...marketing,
  ...invoices,
  ...users,
  ...items,
  ...vehicles,
  ...sidebar,
}

export default zh
