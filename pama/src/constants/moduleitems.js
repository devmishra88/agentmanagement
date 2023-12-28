import {
  Diversity3Icon,
  AccountCircleIcon,
  NewspaperIcon,
  MapsHomeWorkIcon,
  InventoryIcon,
  BarChartIcon,
  ReceiptLongIcon,
  ReportIcon,
  SettingsIcon,
  ManageAccountsIcon,
  LockResetIcon,
} from "./index";

import { Dashboard, AreaForm, ManageTemplate, ManageArea, ManageNewspaper, ManageAgent } from "../screens";

export const screens = [
    { screen: <Dashboard />, navlink: `dashboard` },
    { screen: <ManageTemplate />, navlink: `managetemplate` },
  ];

export const moduleitems = [
  {
    iconname: <AccountCircleIcon />,
    title: `Agency`,
    link: `/agencies`,
    iconcolor: `#b4c100`,
    titlecolor: `#007aff`,
    screen: <ManageTemplate />,
  },
  {
    iconname: <Diversity3Icon />,
    title: `Agents`,
    link: `/agents`,
    iconcolor: `#d32d41`,
    titlecolor: `#007aff`,
    screen: <ManageAgent />,
  },
  {
    iconname: <NewspaperIcon />,
    title: `Newspaper`,
    link: `/newspapers`,
    iconcolor: `#4cb5f6`,
    titlecolor: `#007aff`,
    screen: <ManageNewspaper />,
  },
  {
    iconname: <MapsHomeWorkIcon />,
    title: `Area`,
    link: `/areas`,
    iconcolor: `#b307f7`,
    titlecolor: `#007aff`,
    screen: <ManageArea />,
  },
  {
    iconname: <InventoryIcon />,
    title: `Purchase`,
    link: `/purchases`,
    iconcolor: `#b4c100`,
    titlecolor: `#007aff`,
    screen: <ManageTemplate />,
  },
  {
    iconname: <BarChartIcon />,
    title: `Sales`,
    link: `/sales`,
    iconcolor: `#d32d41`,
    titlecolor: `#007aff`,
    screen: <ManageTemplate />,
  },
  {
    iconname: <ReceiptLongIcon />,
    title: `Billing`,
    link: `/billings`,
    iconcolor: `#007aff`,
    titlecolor: `#007aff`,
    screen: <ManageTemplate />,
  },
  {
    iconname: <ReportIcon />,
    title: `Report`,
    link: `/reports`,
    iconcolor: `#4cb5f6`,
    titlecolor: `#007aff`,
    screen: <ManageTemplate />,
  },
];

export const configuration = [
  {
    iconname: <ManageAccountsIcon />,
    title: `Manage Profile`,
    link: `/profile`,
    iconcolor: `#d32d41`,
    titlecolor: `#007aff`,
    screen: <ManageTemplate />,
  },
  {
    iconname: <LockResetIcon />,
    title: `Change Password`,
    link: `/changepassword`,
    iconcolor: `#2196f3`,
    titlecolor: `#007aff`,
    screen: <ManageTemplate />,
  },
  {
    iconname: <SettingsIcon />,
    title: `Settings`,
    link: `/settings`,
    iconcolor: `#7e909a`,
    titlecolor: `#007aff`,
    screen: <ManageTemplate />,
  },
];
