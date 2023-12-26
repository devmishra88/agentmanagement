import Login from "./Login";
import Forgot from "./Forgot";
import Dashboard from "./Dashboard";
import AreaForm from "./AreaForm";
import ManageArea from "./ManageArea";
import AddAgent from "./AddAgent";
import ManageTemplate from "./ManageTemplate";
import NewspaperForm from "./NewspaperForm";
import ManageNewspaper from "./ManageNewspaper"; 

const publicscreens = [
  { screen: <Login />, navlink: `/` },
  { screen: <Forgot />, navlink: `/forgot` },
];

const secureaddscreens = [
  { screen: <AreaForm />, navlink: `/area` },
  { screen: <NewspaperForm />, navlink: `/newspaper` },
];

export {
  Login,
  Forgot,
  Dashboard,
  AddAgent,
  AreaForm,
  ManageArea,
  ManageTemplate,
  NewspaperForm,
  ManageNewspaper,
  publicscreens,
  secureaddscreens,
};
