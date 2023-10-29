import Login from "./Login";
import Forgot from "./Forgot";
import Dashboard from "./Dashboard";
import AddArea from "./AddArea";
import ManageArea from "./ManageArea";
import AddAgent from "./AddAgent";
import ManageTemplate from "./ManageTemplate";

const publicscreens = [
  { screen: <Login />, navlink: `/` },
  { screen: <Forgot />, navlink: `/forgot` },
];

const secureaddscreens = [
  //   { screen: <Dashboard />, navlink: `dashboard` },
  //   { screen: <ManageArea />, navlink: `areas` },
  //   { screen: <ManageTemplate />, navlink: `template` },
  //   { screen: <ManageTemplate />, navlink: `agencys` },
  //   { screen: <ManageTemplate />, navlink: `agents` },
  //   { screen: <ManageTemplate />, navlink: `newspapers` },
  //   { screen: <ManageTemplate />, navlink: `purchases` },
  //   { screen: <ManageTemplate />, navlink: `sales` },
  //   { screen: <ManageTemplate />, navlink: `billings` },
  //   { screen: <ManageTemplate />, navlink: `reports` },
  //   { screen: <ManageTemplate />, navlink: `profile` },
  //   { screen: <ManageTemplate />, navlink: `changepassword` },
  //   { screen: <ManageTemplate />, navlink: `settings` },
  { screen: <AddArea />, navlink: `/addarea` },
];

export {
  Login,
  Forgot,
  Dashboard,
  AddAgent,
  AddArea,
  ManageArea,
  ManageTemplate,
  publicscreens,
  secureaddscreens,
};
