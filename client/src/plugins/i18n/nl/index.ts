import common from './common.json'
import equipments from './equipments.json'
import invoices from './invoices.json'
import marketing from './marketing.json'
import premium from './premium.json'
import sidebar from './sidebar.json'
import teams from './teams.json'
import timesheets from './timesheets.json'
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
  ...equipments,
  ...teams,
  ...timesheets,
  ...sidebar,
}

export default nl
