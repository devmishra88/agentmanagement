import Login from "./Login";
import Forgot from "./Forgot";
import Dashboard from "./Dashboard";
import AddArea from "./AddArea";
import ManageArea from "./ManageArea";
import AddAgent from "./AddAgent";
import ManageTemplate from "./ManageTemplate";

const publicscreens = [
  { screen: <Login />, navlink: `` },
  { screen: <Forgot />, navlink: `forgot` },
];

const securescreens = [
  { screen: <Dashboard />, navlink: `dashboard` },
  { screen: <AddArea />, navlink: `addarea` },
  { screen: <ManageArea />, navlink: `managearea` },
  { screen: <AddAgent />, navlink: `addagent` },
  { screen: <ManageTemplate />, navlink: `manageagents` },
  { screen: <ManageTemplate />, navlink: `manageagency` },
  { screen: <ManageTemplate />, navlink: `managenewspapers` },
  { screen: <ManageTemplate />, navlink: `managepurchase` },
  { screen: <ManageTemplate />, navlink: `managesales` },
  { screen: <ManageTemplate />, navlink: `managebilling` },
  { screen: <ManageTemplate />, navlink: `reports` },
  { screen: <ManageTemplate />, navlink: `profile` },
  { screen: <ManageTemplate />, navlink: `changepassword` },
  { screen: <ManageTemplate />, navlink: `settings` },
  { screen: <ManageTemplate />, navlink: `managetemplate` },
];

export {
  Login,
  Forgot,
  Dashboard,
  AddAgent,
  AddArea,
  ManageArea,
  ManageTemplate,
};
