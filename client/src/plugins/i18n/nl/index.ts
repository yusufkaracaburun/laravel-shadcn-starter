import common from './common.json'
import invoices from './invoices.json'
import marketing from './marketing.json'
import premium from './premium.json'
import sidebar from './sidebar.json'
import users from './users.json'
import vehicles from './vehicles.json'

/**
 * Merged Dutch translations
 * All translation files are imported and merged into a single object
 */
const nl = {
  ...common,
  ...premium,
  ...marketing,
  ...invoices,
  ...users,
  ...vehicles,
  ...sidebar,
}

export default nl
